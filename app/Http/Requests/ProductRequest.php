<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()) { return false; }
        $product = $this->route('product');
        return $product instanceof Product
            ? $this->user()->can('update', $product)
            : $this->user()->can('create', Product::class);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('sku'))) {
            $this->merge(['sku' => strtoupper(trim($this->input('sku')))]);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $uniqueSku = Rule::unique('products', 'sku')->where('user_id', $this->user()->id);
        if ($product instanceof Product) { $uniqueSku->ignore($product); }

        return [
            'name' => ['bail', 'required', 'string', 'min:3', 'max:150'],
            'sku' => ['bail', 'required', 'string', 'max:24', new ProductSku, $uniqueSku],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999.99'],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'published_at' => ['nullable', 'required_if:status,active', 'date_format:Y-m-d', 'before_or_equal:today'],
            'remove_image' => ['sometimes', 'boolean'],
            'image' => [
                Rule::requiredIf(!($product instanceof Product)), 'nullable',
                'prohibited_if:remove_image,1', 'file', 'image',
                'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
            ],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === 'active' && is_numeric($this->input('stock')) && (int)$this->input('stock') === 0) {
                $validator->errors()->add('stock', 'An active product must have at least one item in stock.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'You already have a product with this SKU.',
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
            'published_at.required_if' => 'Active products need a publication date.',
            'image.required' => 'Please upload a product image.',
            'image.max' => 'The image must not exceed 2 MB.',
            'image.dimensions' => 'Use an image between 100×100 and 4000×4000 pixels.',
            'image.prohibited_if' => 'Choose either a replacement image or image removal.',
        ];
    }

    public function attributes(): array
    {
        return ['sku' => 'SKU', 'published_at' => 'publication date', 'sale_price' => 'sale price'];
    }
}
