<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductSku implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/\APRD-[A-Z0-9]{4,20}\z/', $value)) {
            $fail('The :attribute must start with PRD- followed by 4 to 20 letters or numbers.');
        }
    }
}
