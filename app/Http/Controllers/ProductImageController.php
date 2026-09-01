<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['admin'];
    }

    public function destroy(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);
        $tadinyaUtama = $image->is_primary;

        DB::transaction(function () use ($product, $image, $tadinyaUtama) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
            if ($tadinyaUtama) {
                $product->images()->oldest('id')->first()?->update(['is_primary' => true]);
            }
        });

        return back()->with('sukses', 'Gambar dihapus.');
    }

    public function setPrimary(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);
        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('sukses', 'Gambar utama diganti.');
    }
}
