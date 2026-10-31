<span class="badge-status status-{{ in_array($product->status, ['active', 'draft', 'archived']) ? $product->status : 'draft' }}">{{ ucfirst($product->status) }}</span>
