<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $categories = Category::query()
            ->withCount([
                'products',
                'products as active_products_count' => fn ($q) => $q->where('is_active', true),
            ])
            ->when($q !== '', fn ($query) => $query->where('name', 'ilike', "%{$q}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'unique:categories,name',
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required'   => 'Nama kategori wajib diisi.',
            'name.string'     => 'Nama kategori harus berupa teks.',
            'name.min'        => 'Nama kategori minimal 2 karakter.',
            'name.max'        => 'Nama kategori maksimal 100 karakter.',
            'name.unique'     => 'Nama kategori itu sudah dipakai.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('sukses', 'Kategori berhasil ditambah.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required'   => 'Nama kategori wajib diisi.',
            'name.string'     => 'Nama kategori harus berupa teks.',
            'name.min'        => 'Nama kategori minimal 2 karakter.',
            'name.max'        => 'Nama kategori maksimal 100 karakter.',
            'name.unique'     => 'Nama kategori itu sudah dipakai kategori lain.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('sukses', 'Kategori berhasil diubah.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with(
                'gagal',
                "Kategori \"{$category->name}\" gak bisa dihapus karena masih dipakai "
                . $category->products()->count() . " produk."
            );
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('sukses', 'Kategori dihapus.');
    }
}
