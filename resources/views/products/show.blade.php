@extends('layouts.app')
@section('title', 'Product details')
@section('content')
<a class="back-link" href="{{ route('products.index') }}"><i class="bi bi-arrow-left"></i> Back to inventory</a>
<div class="page-heading"><div><div class="eyebrow">THE WHOLE PICTURE</div><h1 class="text-break">{{ $product->name }}</h1><p class="text-secondary">{{ $product->sku }}</p></div><a class="btn btn-primary" href="{{ route('products.edit', $product) }}"><i class="bi bi-pencil"></i> Edit product</a></div>
<section class="panel"><div class="section-body"><div class="row g-4 g-lg-5"><div class="col-md-5">@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="detail-image">@else<div class="image-placeholder"><i class="bi bi-image"></i><span>No product image</span></div>@endif</div><div class="col-md-7">
    @include('products.partials.status')
    <dl class="detail-list"><div><dt>Regular price</dt><dd>₹{{ number_format((float)$product->price, 2) }}</dd></div><div><dt>Sale price</dt><dd>{{ $product->sale_price !== null ? '₹'.number_format((float)$product->sale_price,2) : 'Not on sale' }}</dd></div><div><dt>Stock available</dt><dd>{{ number_format($product->stock) }} units</dd></div><div><dt>Publication date</dt><dd>{{ $product->published_at?->format('d M Y') ?? 'Not published' }}</dd></div><div><dt>Added to catalog</dt><dd>{{ $product->created_at->format('d M Y') }}</dd></div><div><dt>Last updated</dt><dd>{{ $product->updated_at->format('d M Y, H:i') }}</dd></div></dl>
    <h2 class="section-heading">About this product</h2><p class="description">{{ $product->description ?: 'No description added yet.' }}</p>
    <form action="{{ route('products.destroy', $product) }}" method="POST" class="js-confirm-delete mt-4">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i> Delete product</button></form>
</div></div></div></section>
@endsection
