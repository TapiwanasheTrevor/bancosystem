<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\CreditPricing;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $catalogType = $request->query('type', 'microbiz');
        
        // Get categories based on catalog type if provided, otherwise get all
        if ($request->has('type')) {
            $categories = Category::with(['children' => function($query) use ($catalogType) {
                $query->where('catalog_type', $catalogType);
            }])
            ->whereNull('parent_id')
            ->where('catalog_type', $catalogType)
            ->get();
            
            $products = Product::with('category', 'creditPricings')
                ->where('catalog_type', $catalogType)
                ->get();
        } else {
            $categories = Category::with('children')->whereNull('parent_id')->get();
            $products = Product::with('category', 'creditPricings')->get();
        }
        
        return view('admin.products.index', compact('categories', 'products', 'catalogType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'catalog_type' => 'required|in:microbiz,hirepurchase',
        ]);

        // Handle image upload to `public/products`
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/products'), $imageName);
        }

        // Get the category to determine catalog type if not explicitly provided
        $category = Category::find($request->category_id);
        
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'image' => $imageName ?? null,
            'catalog_type' => $request->catalog_type ?? $category->catalog_type ?? 'microbiz',
        ]);

        // Save credit pricing for different months
        foreach ($request->credit as $months => $data) {
            //if
            if ($data['interest'] != 0) {
                CreditPricing::create([
                    'product_id' => $product->id,
                    'months' => (int)$months,  // Ensure it's an integer
                    'interest' => (float)$data['interest'], // Ensure it's a float
                    'final_price' => $data['interest'],
                ]);
            }
        }

        return redirect('/products')->with('success', 'Product added successfully.');
    }

    public function list(Request $request)
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();

        if ($request->ajax()) {
            $query = Product::with('category');

            // Apply filters dynamically
            if (!empty($request->search)) {
                $query->where('name', 'LIKE', "%{$request->search}%");
            }

            if (!empty($request->category)) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }

            if (!empty($request->catalog_type)) {
                $query->where('catalog_type', $request->catalog_type);
            }

            return DataTables::of($query)
                ->addColumn('image', function ($product) {
                    return asset('images/products/' . $product->image) ?: 'No Image';
                })
                ->addColumn('actions', function ($product) {
                    return '
                    <button onclick="openProductModal(' . $product->id . ')" class="text-blue-600">Edit</button>
                    <button onclick="deleteProduct(' . $product->id . ')" class="text-red-600">Delete</button>
                ';
                })
                ->rawColumns(['image', 'actions']) // Ensures HTML rendering in DataTables
                ->make(true);
        }

        return view('admin.products.view')->with('categories', $categories);
    }
}
