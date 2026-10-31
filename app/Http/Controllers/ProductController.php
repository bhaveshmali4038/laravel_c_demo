<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);
        return view('products.index', ['stats' => $this->inventoryStats($request->user())]);
    }

    public function data(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);
        $request->validate([
            'length' => ['sometimes', 'integer', Rule::in([10, 25, 50, 100])],
            'status' => ['nullable', Rule::in(['draft', 'active', 'archived'])],
        ]);
        $query = $request->user()->products()->select('products.*');
        if ($request->filled('status')) { $query->where('status', $request->input('status')); }

        return DataTables::eloquent($query)
            ->whitelist(['id', 'name', 'sku', 'price', 'stock', 'status', 'published_at'])
            ->editColumn('price', fn (Product $product): string => number_format((float)$product->price, 2))
            ->editColumn('published_at', fn (Product $product): string => $product->published_at?->format('d M Y') ?? '—')
            ->addColumn('image', fn (Product $product): string => view('products.partials.image', compact('product'))->render())
            ->editColumn('status', fn (Product $product): string => view('products.partials.status', compact('product'))->render())
            ->addColumn('actions', fn (Product $product): string => view('products.partials.actions', compact('product'))->render())
            ->rawColumns(['image', 'status', 'actions'])->toJson();
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);
        return view('products.form', ['product' => new Product(['status' => 'draft', 'stock' => 0])]);
    }

    public function store(ProductRequest $request, ProductService $service): RedirectResponse
    {
        $service->save($request->user()->products()->make(), Arr::except($request->validated(), ['image', 'remove_image']), $request->file('image'));
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        Gate::authorize('view', $product);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);
        return view('products.form', compact('product'));
    }

    public function update(ProductRequest $request, Product $product, ProductService $service): RedirectResponse
    {
        Gate::authorize('update', $product);
        $service->save($product, Arr::except($request->validated(), ['image', 'remove_image']), $request->file('image'), $request->boolean('remove_image'));
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product, ProductService $service): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $product);
        $service->delete($product);
        return $request->expectsJson()
            ? response()->json(['message' => 'Product deleted successfully.', 'stats' => $this->inventoryStats($request->user())])
            : redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
    private function inventoryStats(User $user): array
    {
        $query = $user->products();
        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'low' => (clone $query)->where('stock', '<=', 5)->where('status', '!=', 'archived')->count(),
            'value' => (clone $query)->selectRaw('COALESCE(SUM(price * stock), 0) as total')->value('total'),
        ];
    }
}
