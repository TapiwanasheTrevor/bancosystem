<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function microbizCategories()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('catalog_type', 'microbiz')
            ->get();
        return view('admin.categories.index', [
            'categories' => $categories,
            'catalogType' => 'microbiz'
        ]);
    }

    public function hirePurchaseCategories()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('catalog_type', 'hirepurchase')
            ->get();
        return view('admin.categories.index', [
            'categories' => $categories,
            'catalogType' => 'hirepurchase'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:categories,id',
            'catalog_type' => 'required|in:microbiz,hirepurchase',
        ]);

        // If parent category exists, use its catalog type
        $parentCatalogType = null;
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            if ($parent) {
                $parentCatalogType = $parent->catalog_type;
            }
        }

        // Create category with either specified catalog type or parent's catalog type
        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'catalog_type' => $parentCatalogType ?? $request->catalog_type,
        ]);

        // Redirect to the appropriate catalog type page
        if (($parentCatalogType ?? $request->catalog_type) === 'hirepurchase') {
            return redirect('/hirepurchase/categories')->with('success', 'Category added successfully.');
        } else {
            return redirect('/microbiz/categories')->with('success', 'Category added successfully.');
        }
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $catalogType = $category->catalog_type;
        $category->delete();
        
        if ($catalogType === 'hirepurchase') {
            return redirect('/hirepurchase/categories')->with('success', 'Category deleted.');
        } else {
            return redirect('/microbiz/categories')->with('success', 'Category deleted.');
        }
    }
}
