<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('kind')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'kind' => ['required', Rule::in(['income', 'expense'])],
        ]);

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'kind' => ['required', Rule::in(['income', 'expense'])],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori dihapus.');
    }
}
