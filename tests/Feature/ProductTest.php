<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Test Product', 'sku' => 'PRD-TEST01', 'description' => 'A useful product.',
            'price' => '100.00', 'sale_price' => '80.00', 'stock' => 10, 'status' => 'active',
            'published_at' => now()->toDateString(), 'image' => UploadedFile::fake()->image('product.png', 300, 300),
        ], $overrides);
    }

    private function product(User $user, array $overrides = []): Product
    {
        return $user->products()->create(array_replace([
            'name' => 'Existing Product', 'sku' => 'PRD-TEST01', 'price' => '100.00', 'stock' => 5, 'status' => 'draft',
        ], $overrides));
    }

    private function tableRequest(array $overrides = []): array
    {
        $names = ['id','image','name','sku','price','stock','status','published_at','actions'];
        return array_replace_recursive([
            'draw' => 1, 'start' => 0, 'length' => 10, 'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 0, 'dir' => 'desc']],
            'columns' => array_map(fn ($name) => ['data' => $name, 'name' => $name, 'searchable' => in_array($name, ['image','actions']) ? 'false' : 'true', 'orderable' => in_array($name, ['image','actions']) ? 'false' : 'true', 'search' => ['value' => '', 'regex' => 'false']], $names),
        ], $overrides);
    }

    public function test_guests_cannot_access_inventory_or_write_products(): void
    {
        $this->get('/products')->assertRedirect(route('login'));
        $this->post('/products')->assertRedirect(route('login'));
        $this->getJson('/products/data')->assertUnauthorized();
    }

    public function test_verified_owner_can_render_all_product_pages(): void
    {
        $user = User::factory()->create();
        $product = $this->product($user);
        $this->actingAs($user);
        foreach (['/products','/products/create',"/products/{$product->id}","/products/{$product->id}/edit"] as $url) {
            $this->get($url)->assertOk()->assertSee('Stockroom');
        }
    }

    public function test_create_stores_image_and_ignores_client_supplied_ownership(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->post('/products', $this->payload(['sku' => ' prd-test01 ', 'user_id' => $other->id, 'image_path' => 'injected.png']))
            ->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $product = Product::firstOrFail();
        $this->assertEquals($user->id, $product->user_id);
        $this->assertSame('PRD-TEST01', $product->sku);
        $this->assertNotEquals('injected.png', $product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_update_keeps_then_replaces_then_removes_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/products', $this->payload())->assertSessionHasNoErrors();
        $product = Product::firstOrFail();
        $original = $product->image_path;
        $payload = $this->payload(['name' => 'Updated Product']);
        unset($payload['image']);
        $this->put('/products/'.$product->id, $payload)->assertSessionHasNoErrors();
        $this->assertSame($original, $product->fresh()->image_path);
        $payload['image'] = UploadedFile::fake()->image('replacement.jpg', 400, 400);
        $this->put('/products/'.$product->id, $payload)->assertSessionHasNoErrors();
        $replacement = $product->fresh()->image_path;
        $this->assertNotSame($original, $replacement);
        Storage::disk('public')->assertMissing($original);
        Storage::disk('public')->assertExists($replacement);
        unset($payload['image']);
        $payload['remove_image'] = '1';
        $this->put('/products/'.$product->id, $payload)->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->image_path);
        Storage::disk('public')->assertMissing($replacement);
    }

    public function test_deletion_removes_record_and_image_for_json_and_html_requests(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/products', $this->payload());
        $product = Product::firstOrFail();
        $path = $product->image_path;
        $this->deleteJson('/products/'.$product->id)->assertOk()->assertJson(['message' => 'Product deleted successfully.']);
        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing($path);
        $product = $this->product($user);
        $this->delete('/products/'.$product->id)->assertRedirect(route('products.index'));
    }

    public function test_another_users_product_is_forbidden_for_every_operation(): void
    {
        $owner = User::factory()->create();
        $product = $this->product($owner);
        $this->actingAs(User::factory()->create());
        $this->get('/products/'.$product->id)->assertForbidden();
        $this->get('/products/'.$product->id.'/edit')->assertForbidden();
        $this->put('/products/'.$product->id, [])->assertForbidden();
        $this->deleteJson('/products/'.$product->id)->assertForbidden();
        $this->assertModelExists($product);
    }

    public function test_required_numeric_custom_and_conditional_validation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post('/products', [])->assertSessionHasErrors(['name','sku','price','stock','status','image']);
        $this->post('/products', $this->payload(['sku' => 'BAD', 'price' => '-1', 'sale_price' => '200', 'stock' => 0, 'published_at' => null]))
            ->assertSessionHasErrors(['sku','price','sale_price','stock','published_at']);
        $this->post('/products', $this->payload(['published_at' => now()->addDay()->toDateString(), 'price' => '1.234', 'status' => 'invalid']))
            ->assertSessionHasErrors(['published_at','price','status']);
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_sku_is_unique_per_owner_but_allowed_for_another_owner(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $this->product($owner);
        $this->actingAs($owner)->post('/products', $this->payload())->assertSessionHasErrors('sku');
        $this->actingAs(User::factory()->create())->post('/products', $this->payload())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('products', 2);
    }

    public function test_image_rules_reject_bad_type_size_dimensions_and_conflicting_actions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);
        foreach ([
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('large.png', 300, 300)->size(2049),
            UploadedFile::fake()->image('tiny.png', 50, 50),
            UploadedFile::fake()->createWithContent('renamed.jpg', '<script>alert(1)</script>'),
        ] as $file) {
            $this->post('/products', $this->payload(['image' => $file]))->assertSessionHasErrors('image');
        }
        $product = $this->product($user);
        $this->put('/products/'.$product->id, $this->payload(['remove_image' => '1']))->assertSessionHasErrors('image');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_datatables_paginates_searches_sorts_filters_and_scopes_to_owner(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 12; $i++) {
            $this->product($user, ['name' => sprintf('Product %02d', $i), 'sku' => sprintf('PRD-TEST%02d', $i), 'price' => $i * 10, 'status' => $i <= 8 ? 'active' : 'draft']);
        }
        $this->product(User::factory()->create(), ['name' => 'Private other product']);
        $this->actingAs($user);
        $data = $this->tableRequest();
        $url = '/products/data?'.http_build_query($data);
        $this->getJson($url)->assertOk()->assertJsonPath('recordsTotal', 12)->assertJsonCount(10, 'data')->assertJsonPath('data.0.name', 'Product 12')->assertDontSee('Private other product');
        $data['start'] = 10;
        $this->getJson('/products/data?'.http_build_query($data))->assertJsonCount(2, 'data');
        $data['start'] = 0;
        $data['search']['value'] = 'TEST03';
        $this->getJson('/products/data?'.http_build_query($data))->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.sku', 'PRD-TEST03');
        $data['search']['value'] = '';
        $data['order'] = [['column' => 4, 'dir' => 'asc']];
        $this->getJson('/products/data?'.http_build_query($data))->assertJsonPath('data.0.price', '10.00');
        $data['status'] = 'draft';
        $this->getJson('/products/data?'.http_build_query($data))->assertJsonCount(4, 'data');
        $data['length'] = -1;
        $this->getJson('/products/data?'.http_build_query($data))->assertUnprocessable();
    }

    public function test_datatables_and_detail_page_escape_untrusted_text(): void
    {
        $user = User::factory()->create();
        $product = $this->product($user, ['name' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(1)>']);
        $this->actingAs($user)->get('/products/'.$product->id)->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $response = $this->getJson('/products/data?'.http_build_query($this->tableRequest()))->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->json('data.0.name'));
        $this->assertStringNotContainsString('<script>', $response->json('data.0.actions'));
    }

    public function test_failed_database_write_cleans_up_new_upload(): void
    {
        Storage::fake('public');
        $product = User::factory()->create()->products()->make();
        try {
            app(ProductService::class)->save($product, ['sku' => 'PRD-FAIL01'], UploadedFile::fake()->image('orphan.png', 200, 200));
            $this->fail('Expected a database constraint failure.');
        } catch (QueryException) {
            $this->assertSame([], Storage::disk('public')->allFiles());
            $this->assertDatabaseCount('products', 0);
        }
    }
}
