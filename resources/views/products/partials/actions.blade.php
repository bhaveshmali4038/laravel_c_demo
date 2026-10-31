<div class="action-buttons">
<a class="action-button" href="{{ route('products.show', $product) }}" title="View product" aria-label="View {{ $product->name }}"><i class="bi bi-eye"></i></a>
<a class="action-button" href="{{ route('products.edit', $product) }}" title="Edit product" aria-label="Edit {{ $product->name }}"><i class="bi bi-pencil"></i></a>
<button class="action-button danger js-delete-product" type="button" data-url="{{ route('products.destroy', $product) }}" title="Delete product" aria-label="Delete {{ $product->name }}"><i class="bi bi-trash3"></i></button>
</div>
