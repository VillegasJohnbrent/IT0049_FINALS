<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Support\Images;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $archived = $request->boolean('archived');
        $products = Product::query()->when($archived, fn ($query) => $query->onlyTrashed())
            ->orderBy('name')->orderBy('id')->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'archived'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->safe()->except('original_stock_quantity');
        Images::save(new Product, $data, 'image', 'products');

        return redirect()->route('products.index')->with('success', 'Product added.');
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->safe()->except('original_stock_quantity');
        Images::save($product, $data, 'image', 'products', function ($record) use ($request) {
            if ($record->stock_quantity !== (int) $request->validated('original_stock_quantity')) {
                throw ValidationException::withMessages([
                    'stock_quantity' => 'Stock changed while you were editing. Reload this page before saving.',
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product archived.');
    }

    public function restore(Product $product)
    {
        $product->restore();

        return redirect()->route('products.index')->with('success', 'Product restored.');
    }
}
