<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(['email' => 'demo@example.com'], [
            'name' => 'Alex Morgan', 'password' => 'DemoPass!2026', 'email_verified_at' => now(),
        ]);

        $items = [
            ['Everyday ceramic mug', 'MUG001', 649, 549, 48, 'active', 'mug', [226, 232, 218]],
            ['Canvas carryall tote', 'BAG001', 1299, null, 32, 'active', 'bag', [235, 225, 208]],
            ['Studio wireless headphones', 'AUDIO1', 4999, 4499, 16, 'active', 'audio', [222, 228, 234]],
            ['Pocket journal · sage', 'NOTE01', 449, null, 4, 'active', 'book', [224, 233, 219]],
            ['Pour-over coffee set', 'BREW01', 2199, 1899, 12, 'active', 'mug', [233, 221, 208]],
            ['Weekender travel bag', 'TRAVEL1', 3499, null, 0, 'draft', 'bag', [224, 228, 216]],
            ['Minimal desk clock', 'CLOCK1', 1899, 1599, 22, 'active', 'clock', [232, 227, 215]],
            ['Linen notebook · sand', 'NOTE02', 549, null, 3, 'active', 'book', [235, 229, 215]],
            ['Insulated water bottle', 'BOTTLE1', 999, 849, 41, 'active', 'bottle', [220, 232, 226]],
            ['Analog everyday watch', 'WATCH1', 3999, null, 8, 'active', 'watch', [225, 224, 231]],
            ['Compact desktop speaker', 'SPEAK1', 2499, 2199, 14, 'active', 'speaker', [225, 231, 227]],
            ['Soft-touch pencil case', 'CASE01', 399, null, 0, 'archived', 'bag', [235, 224, 218]],
            ['Travel sketchbook', 'SKETCH1', 799, null, 6, 'draft', 'book', [230, 232, 218]],
            ['Weekend coffee tumbler', 'CUP001', 899, 799, 27, 'active', 'bottle', [223, 230, 234]],
            ['Overnight essentials bag', 'POUCH1', 1199, null, 5, 'draft', 'bag', [231, 221, 214]],
            ['Edition desk speaker', 'SPEAK2', 2999, null, 9, 'archived', 'speaker', [229, 225, 218]],
        ];

        foreach ($items as $i => [$name, $sku, $price, $sale, $stock, $status, $shape, $background]) {
            if ($user->products()->where('sku', 'PRD-'.$sku)->exists()) { continue; }
            $path = 'products/demo-'.strtolower($sku).'.png';
            Storage::disk('public')->put($path, $this->illustration($shape, $background));
            $product = $user->products()->make([
                'name' => $name, 'sku' => 'PRD-'.$sku,
                'description' => 'Thoughtfully designed for everyday use. '.$name.' brings simple materials and useful details together. A sample product for exploring your Stockroom workspace.',
                'price' => $price, 'sale_price' => $sale, 'stock' => $stock, 'status' => $status,
                'published_at' => $status === 'active' ? now()->subDays(20 - $i)->toDateString() : null,
            ]);
            $product->image_path = $path;
            $product->save();
        }
    }

    /** Original procedural product illustrations: no remote files required. */
    private function illustration(string $shape, array $background): string
    {
        $im = imagecreatetruecolor(480, 480);
        imageantialias($im, true);
        $bg = imagecolorallocate($im, ...$background);
        $dark = imagecolorallocate($im, 65, 83, 70);
        $mid = imagecolorallocate($im, 133, 151, 122);
        $light = imagecolorallocate($im, 244, 241, 225);
        $shadow = imagecolorallocate($im, $background[0]-16, $background[1]-16, $background[2]-16);
        imagefill($im, 0, 0, $bg);
        imagefilledellipse($im, 240, 375, 245, 30, $shadow);
        imagesetthickness($im, 14);
        switch ($shape) {
            case 'mug':
                imageellipse($im, 337, 249, 100, 115, $light);
                imagefilledrectangle($im, 130, 177, 305, 318, $light);
                imagefilledellipse($im, 217, 318, 175, 70, $light);
                imagefilledellipse($im, 217, 177, 175, 45, $mid);
                imagefilledellipse($im, 217, 181, 144, 27, $dark);
                break;
            case 'bag':
                imagearc($im, 240, 169, 125, 142, 180, 360, $dark);
                imagefilledpolygon($im, [132,178,348,178,365,351,115,351], $mid);
                imageline($im, 174, 193, 174, 330, $dark);
                imageline($im, 305, 193, 305, 330, $dark);
                imagefilledrectangle($im, 204, 238, 276, 267, $light);
                break;
            case 'book':
                imagefilledrectangle($im, 145, 116, 330, 355, $dark);
                imagefilledrectangle($im, 160, 110, 337, 345, $light);
                imagefilledrectangle($im, 163, 110, 339, 333, $mid);
                imageline($im, 180, 111, 180, 333, $dark);
                imagefilledrectangle($im, 211, 161, 294, 171, $light);
                imagefilledrectangle($im, 222, 181, 284, 185, $light);
                break;
            case 'audio':
                imagesetthickness($im, 32);
                imagearc($im, 240, 240, 210, 240, 175, 365, $dark);
                imagefilledellipse($im, 137, 284, 69, 123, $dark);
                imagefilledellipse($im, 343, 284, 69, 123, $dark);
                imagefilledellipse($im, 149, 284, 38, 99, $mid);
                imagefilledellipse($im, 331, 284, 38, 99, $mid);
                break;
            case 'clock':
                imagefilledellipse($im, 240, 240, 236, 236, $dark);
                imagefilledellipse($im, 240, 240, 210, 210, $light);
                imagesetthickness($im, 9);
                imageline($im, 240, 240, 240, 169, $dark);
                imageline($im, 240, 240, 294, 264, $dark);
                imagefilledellipse($im, 240, 240, 15, 15, $dark);
                break;
            case 'watch':
                imagefilledrectangle($im, 203, 88, 277, 385, $dark);
                imagefilledellipse($im, 240, 236, 157, 157, $mid);
                imagefilledellipse($im, 240, 236, 131, 131, $light);
                imagesetthickness($im, 8);
                imageline($im, 240, 236, 240, 190, $dark);
                imageline($im, 240, 236, 276, 247, $dark);
                break;
            case 'speaker':
                imagefilledrectangle($im, 152, 120, 328, 358, $mid);
                imagefilledellipse($im, 240, 266, 128, 128, $dark);
                imagefilledellipse($im, 240, 266, 75, 75, $mid);
                imagefilledellipse($im, 240, 266, 40, 40, $dark);
                imagefilledellipse($im, 240, 165, 34, 34, $dark);
                break;
            default:
                imagefilledrectangle($im, 198, 99, 282, 135, $dark);
                imagefilledellipse($im, 240, 167, 116, 72, $mid);
                imagefilledrectangle($im, 182, 167, 298, 332, $mid);
                imagefilledellipse($im, 240, 332, 116, 44, $mid);
                imagefilledrectangle($im, 183, 222, 297, 275, $light);
                break;
        }
        ob_start();
        imagepng($im);
        $bytes = ob_get_clean();
        imagedestroy($im);
        return $bytes;
    }
}
