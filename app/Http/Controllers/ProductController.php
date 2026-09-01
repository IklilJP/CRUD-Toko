<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show']),
        ];
    }

    public function index(Request $request)
    {
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $categoryId = $request->integer('category_id') ?: null;

        $products = Product::query()
            ->where('is_active', true)
            ->with(['category', 'primaryImage'])
            ->withCount('images')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('sku', 'ilike', "%{$q}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(),
            'q'          => $q,
            'categoryId' => $categoryId,
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    private function simpanGambar(Product $product, array $files): void
    {
        if ($files === []) {
            return;
        }
        $sudahAdaUtama = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create([
                'path'       => $path,
                'is_primary' => ! $sudahAdaUtama,
            ]);
            $sudahAdaUtama = true;
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name'        => ['required', 'string', 'min:2', 'max:150'],
            'sku'         => ['required', 'string', 'alpha_dash', 'max:50', 'unique:products,sku'],
            'price'       => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock'       => ['required', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['required', 'boolean'],
            'images'      => ['nullable', 'array', 'max:5'],
            'images.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih gak ada di database.',

            'name.required' => 'Nama produk wajib diisi.',
            'name.min'      => 'Nama produk minimal 2 karakter.',
            'name.max'      => 'Nama produk maksimal 150 karakter.',

            'sku.required'   => 'SKU wajib diisi.',
            'sku.alpha_dash' => 'SKU cuma boleh huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'sku.unique'     => 'SKU itu sudah dipakai produk lain.',

            'price.required' => 'Harga wajib diisi.',
            'price.numeric'  => 'Harga harus berupa angka.',
            'price.min'      => 'Harga gak boleh minus.',

            'stock.required' => 'Stok wajib diisi.',
            'stock.integer'  => 'Stok harus bilangan bulat, gak boleh ada koma.',
            'stock.min'      => 'Stok gak boleh minus.',

            'description.max' => 'Deskripsi maksimal 2000 karakter.',

            'images.max'     => 'Maksimal 5 gambar sekali unggah.',
            'images.*.image' => 'Berkas ke-:position bukan file gambar.',
            'images.*.mimes' => 'Gambar ke-:position harus jpg, jpeg, png, atau webp.',
            'images.*.max'   => 'Ukuran gambar ke-:position maksimal 2 MB.',
        ]);

        unset($data['images']);
        $data['is_active'] = $request->boolean('is_active');

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->simpanGambar($product, $request->file('images') ?? []);
            return $product;
        });

        return redirect()
            ->route('products.index')
            ->with('sukses', "Produk \"{$product->name}\" berhasil ditambah.");
    }

    public function show(Request $request, Product $product)
    {
        abort_if(! $product->is_active && ! $request->user()?->isAdmin(), 404);

        $product->load(['category', 'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('id')]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('id')]);

        return view('products.edit', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name'        => ['required', 'string', 'min:2', 'max:150'],
            'sku'         => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product),
            ],

            'price'       => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock'       => ['required', 'integer', 'min:0', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['required', 'boolean'],
            'images'      => ['nullable', 'array', 'max:5'],
            'images.*'    => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih gak ada di database.',

            'name.required' => 'Nama produk wajib diisi.',
            'name.min'      => 'Nama produk minimal 2 karakter.',
            'name.max'      => 'Nama produk maksimal 150 karakter.',

            'sku.required'   => 'SKU wajib diisi.',
            'sku.alpha_dash' => 'SKU cuma boleh huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'sku.unique'     => 'SKU itu sudah dipakai produk lain.',

            'price.required' => 'Harga wajib diisi.',
            'price.numeric'  => 'Harga harus berupa angka.',
            'price.min'      => 'Harga gak boleh minus.',

            'stock.required' => 'Stok wajib diisi.',
            'stock.integer'  => 'Stok harus bilangan bulat, gak boleh ada koma.',
            'stock.min'      => 'Stok gak boleh minus.',

            'description.max' => 'Deskripsi maksimal 2000 karakter.',

            'images.max'     => 'Maksimal 5 gambar sekali unggah.',
            'images.*.image' => 'Berkas ke-:position bukan file gambar.',
            'images.*.mimes' => 'Gambar ke-:position harus jpg, jpeg, png, atau webp.',
            'images.*.max'   => 'Ukuran gambar ke-:position maksimal 2 MB.',
        ]);

        unset($data['images']);
        $data['is_active'] = $request->boolean('is_active');
        $sudahAda = $product->images()->count();
        $baru     = count($request->file('images') ?? []);

        if ($sudahAda + $baru > 5) {
            return back()->withInput()->with(
                'gagal',
                "Produk ini sudah punya {$sudahAda} gambar. Maksimal 5 per produk, "
                    . "jadi sisa slotnya cuma " . (5 - $sudahAda) . ". "
                    . "Hapus dulu gambar yang gak kepakai kalau mau ganti."
            );
        }

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);
            $this->simpanGambar($product, $request->file('images') ?? []);
        });

        return redirect()
            //->route('products.edit', $product)
            ->route('products.index')
            ->with('sukses', 'Produk berhasil diubah.');
    }

    public function nonaktifkan(Product $product)
    {
        $product->update(['is_active' => false]);

        return redirect()
            ->route('products.index')
            ->with('sukses', "Produk \"{$product->name}\" dipindahkan ke Arsip.");
    }

    public function arsip()
    {
        $products = Product::query()
            ->where('is_active', false)
            ->with(['category', 'primaryImage'])
            ->withCount('images')
            ->latest('updated_at')
            ->paginate(10);

        return view('products.arsip', compact('products'));
    }

    public function aktifkan(Product $product)
    {
        $product->update(['is_active' => true]);

        return redirect()
            ->route('products.arsip')
            ->with('sukses', "Produk \"{$product->name}\" diaktifkan lagi.");
    }

    public function destroy(Product $product)
    {
        if ($product->is_active) {
            return back()->with('gagal', 'Nonaktifkan dulu produknya sebelum dihapus permanen.');
        }

        // ini masih tes //
        if ($product->transaksiDetails()->exists()) {
            return back()->with(
                'gagal',
                "Produk \"{$product->name}\" pernah dibeli ("
                . $product->transaksiDetails()->count() . " baris nota), jadi gak bisa "
                . "dihapus permanen."
            );
        }
        //xxxxxx//

        $nama = $product->name;
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->delete();
        });

        return redirect()
            ->route('products.arsip')
            ->with('sukses', "Produk \"{$nama}\" dihapus permanen beserta semua gambarnya.");
    }
}
