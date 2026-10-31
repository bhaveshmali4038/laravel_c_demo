<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductService
{
    public function save(Product $product, array $attributes, ?UploadedFile $image = null, bool $removeImage = false): Product
    {
        $newPath = null;
        if ($image) {
            $newPath = $image->store('products', 'public');
            if (!is_string($newPath) || $newPath === '') {
                throw new RuntimeException('The image could not be stored.');
            }
        }
        $oldPath = null;
        try {
            $saved = DB::transaction(function () use ($product, $attributes, $newPath, $removeImage, &$oldPath): Product {
                $current = $product->exists ? Product::query()->lockForUpdate()->findOrFail($product->id) : $product;
                $oldPath = $current->image_path;
                $current->fill($attributes);
                if ($newPath !== null) { $current->image_path = $newPath; }
                elseif ($removeImage) { $current->image_path = null; }
                $current->save();
                return $current;
            });
        } catch (Throwable $exception) {
            $this->deleteFile($newPath);
            throw $exception;
        }
        if ($oldPath !== $saved->image_path) { $this->deleteFile($oldPath); }
        return $saved;
    }

    public function delete(Product $product): void
    {
        $path = DB::transaction(function () use ($product): ?string {
            $current = Product::query()->lockForUpdate()->findOrFail($product->id);
            $path = $current->image_path;
            $current->delete();
            return $path;
        });
        $this->deleteFile($path);
    }

    private function deleteFile(?string $path): void
    {
        if (!$path) { return; }
        try {
            if (!Storage::disk('public')->delete($path)) {
                Log::warning('Product image cleanup failed.', ['path' => $path]);
            }
        } catch (Throwable $exception) { report($exception); }
    }
}
