@if ($product->image_url)
<img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumb" loading="lazy">
@else
<span class="product-thumb no-image" role="img" aria-label="No product image"><i class="bi bi-box"></i></span>
@endif
