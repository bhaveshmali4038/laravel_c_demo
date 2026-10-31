<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_authentication_pages_render_without_vite(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/reset-password/example?email=test@example.com'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('@vite')->assertSee('Stockroom');
        }
    }

    public function test_registration_hashes_password_and_sends_verification(): void
    {
        Notification::fake();
        $this->post('/register', [
            'name' => 'New Member', 'email' => 'new@example.com',
            'password' => 'StrongPass!123', 'password_confirmation' => 'StrongPass!123', 'terms' => '1',
        ])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPass!123', $user->password));
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_duplicate_email_weak_password_and_missing_consent(): void
    {
        User::factory()->create(['email' => 'used@example.com']);
        $this->post('/register', ['name' => 'A', 'email' => 'used@example.com', 'password' => 'short', 'password_confirmation' => 'other'])
            ->assertSessionHasErrors(['name', 'email', 'password', 'terms']);
        $this->assertGuest();
    }

    public function test_login_remember_me_and_logout_work(): void
    {
        $user = User::factory()->create(['password' => 'StrongPass!123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'StrongPass!123', 'remember' => '1'])
            ->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_bad_credentials_are_rate_limited(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Too many attempts. Try again in 60 seconds.']);
        $this->assertGuest();
    }

    public function test_unverified_users_cannot_access_inventory(): void
    {
        $this->actingAs(User::factory()->unverified()->create())->get('/products')->assertRedirect(route('verification.notice'));
    }

    public function test_signed_verification_link_verifies_the_authenticated_user(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('products.index'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_rejects_tampered_and_expired_links(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->get('/email/verify/'.$user->id.'/invalid')->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/email/verify')->assertOk();
        $this->post('/email/verification-notification')->assertSessionHas('status');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_reset_request_does_not_disclose_whether_email_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $known = $this->post('/forgot-password', ['email' => $user->email]);
        $known->assertSessionHas('status');
        $message = session('status');
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])->assertSessionHas('status', $message);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token_and_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $payload = ['email' => $user->email, 'token' => 'wrong', 'password' => 'NewStrong!123', 'password_confirmation' => 'NewStrong!123'];
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $payload['token'] = $token;
        $this->post('/reset-password', $payload)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewStrong!123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create(['password' => 'OldStrong!123']);
        $this->actingAs($user)->get('/account/password')->assertOk();
        $payload = ['current_password' => 'wrong', 'password' => 'NewStrong!123', 'password_confirmation' => 'NewStrong!123'];
        $this->put('/account/password', $payload)->assertSessionHasErrors('current_password');
        $payload['current_password'] = 'OldStrong!123';
        $this->put('/account/password', $payload)->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewStrong!123', $user->fresh()->password));
    }
    public function test_email_case_is_normalized_before_unique_validation(): void
    {
        User::factory()->create(['email' => 'case@example.com']);
        $this->post('/register', [
            'name' => 'Case Member', 'email' => 'CASE@EXAMPLE.COM',
            'password' => 'StrongPass!123', 'password_confirmation' => 'StrongPass!123', 'terms' => '1',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
}
