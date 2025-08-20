<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('name')->paginate(15);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:10',
            'is_active' => 'boolean'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')
                        ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'unit_of_measure' => 'required|string|max:10',
            'is_active' => 'boolean'
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')
                        ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product is used in any invoices
        if ($product->invoiceLineItems()->exists()) {
            return redirect()->back()
                           ->with('error', 'Cannot delete product that is used in invoices.');
        }

        $product->delete();

        return redirect()->route('products.index')
                        ->with('success', 'Product deleted successfully.');
    }
}
