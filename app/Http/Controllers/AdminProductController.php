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

        // Initialize imageName as null
        $imageName = null;
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Ensure the directory exists
                $uploadPath = public_path('images/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Move the file
                $image->move($uploadPath, $imageName);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error uploading image: ' . $e->getMessage())->withInput();
            }
        }

        // Get the category to determine catalog type if not explicitly provided
        $category = Category::find($request->category_id);
        
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'image' => $imageName,
            'catalog_type' => $request->catalog_type ?? $category->catalog_type ?? 'microbiz',
        ]);

        // Check if credit data exists
        if (isset($request->credit)) {
            // Save credit pricing for different months
            foreach ($request->credit as $months => $data) {
                // Default to 0 if not provided or empty
                $interest = !empty($data['interest']) ? (float)$data['interest'] : 0;
                $installmentAmount = !empty($data['installment_amount']) ? (float)$data['installment_amount'] : null;
                
                // Only create entry if either interest or installment amount is provided
                if ($interest > 0 || $installmentAmount > 0) {
                    // Calculate final price based on installment amount if provided
                    if ($installmentAmount) {
                        $finalPrice = $installmentAmount * (int)$months;
                    } else {
                        $finalPrice = $request->base_price * (1 + ($interest / 100));
                    }
                    
                    CreditPricing::create([
                        'product_id' => $product->id,
                        'months' => (int)$months,
                        'interest' => $interest,
                        'installment_amount' => $installmentAmount,
                        'final_price' => $finalPrice,
                    ]);
                }
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
    
    /**
     * Show a product for editing
     */
    public function show($id)
    {
        $product = Product::with(['category', 'creditPricings'])->findOrFail($id);
        return response()->json($product);
    }
    
    /**
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'catalog_type' => 'required|in:microbiz,hirepurchase',
        ]);

        $product = Product::findOrFail($id);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            try {
                // Delete old image if exists
                if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                    unlink(public_path('images/products/' . $product->image));
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Ensure the directory exists
                $uploadPath = public_path('images/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Move the file
                $image->move($uploadPath, $imageName);
                
                // Update image name
                $product->image = $imageName;
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error uploading image: ' . $e->getMessage())->withInput();
            }
        }
        
        // Update product details
        $product->name = $request->name;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->base_price = $request->base_price;
        $product->catalog_type = $request->catalog_type;
        $product->save();
        
        // Update credit pricing
        if (isset($request->credit)) {
            // First delete existing pricing
            CreditPricing::where('product_id', $product->id)->delete();
            
            // Then create new pricing
            foreach ($request->credit as $months => $data) {
                $interest = !empty($data['interest']) ? (float)$data['interest'] : 0;
                $installmentAmount = !empty($data['installment_amount']) ? (float)$data['installment_amount'] : null;
                
                // Only create entry if either interest or installment amount is provided
                if ($interest > 0 || $installmentAmount > 0) {
                    // Calculate final price based on installment amount if provided
                    if ($installmentAmount) {
                        $finalPrice = $installmentAmount * (int)$months;
                    } else {
                        $finalPrice = $request->base_price * (1 + ($interest / 100));
                    }
                    
                    CreditPricing::create([
                        'product_id' => $product->id,
                        'months' => (int)$months,
                        'interest' => $interest,
                        'installment_amount' => $installmentAmount,
                        'final_price' => $finalPrice,
                    ]);
                }
            }
        }
        
        return redirect('/products')->with('success', 'Product updated successfully.');
    }
    
    /**
     * Delete a product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
            unlink(public_path('images/products/' . $product->image));
        }
        
        // Delete credit pricing
        CreditPricing::where('product_id', $product->id)->delete();
        
        // Delete product
        $product->delete();
        
        return redirect('/products')->with('success', 'Product deleted successfully.');
    }
}
