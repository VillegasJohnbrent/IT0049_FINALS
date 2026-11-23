<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product', 'customer', 'staff'])->latest('id')->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::orderBy('name')->get(),
            'customers' => Customer::orderBy('full_name')->get(),
            'reference' => (string) Str::uuid(),
        ]);
    }

    public function store(SaleRequest $request)
    {
        $data = $request->validated();
        $sale = DB::transaction(function () use ($data, $request) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->first();
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => 'This product is no longer available.']);
            }

            // Repeated submissions must not deduct stock twice.
            $existing = Sale::where('reference', $data['reference'])->first();
            if ($existing) {
                if ($existing->sold_by !== $request->user()->id
                    || $existing->product_id !== $product->id
                    || $existing->quantity !== (int) $data['quantity']
                    || $existing->customer_id !== (isset($data['customer_id']) ? (int) $data['customer_id'] : null)) {
                    throw ValidationException::withMessages(['reference' => 'This form was already used. Open Record Sale again.']);
                }
                return $existing;
            }

            $customer = isset($data['customer_id'])
                ? Customer::whereKey($data['customer_id'])->lockForUpdate()->first() : null;
            if (isset($data['customer_id']) && ! $customer) {
                throw ValidationException::withMessages(['customer_id' => 'This customer is no longer available.']);
            }

            $quantity = (int) $data['quantity'];
            $updated = Product::whereKey($product->id)->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);
            if (! $updated) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock. Only '.$product->stock_quantity.' available.',
                ]);
            }

            // Calculate in centavos to avoid floating-point rounding.
            [$pesos, $centavos] = explode('.', $product->price);
            $total = ((int) $pesos * 100 + (int) $centavos) * $quantity;

            return Sale::create([
                'product_id' => $product->id,
                'customer_id' => $customer?->id,
                'sold_by' => $request->user()->id,
                'product_name' => $product->name,
                'customer_name' => $customer?->full_name,
                'staff_name' => $request->user()->full_name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'total_price' => intdiv($total, 100).'.'.str_pad((string) ($total % 100), 2, '0', STR_PAD_LEFT),
                'reference' => $data['reference'],
            ]);
        }, 5);

        return redirect()->route('sales.index')->with('success', 'Sale #'.$sale->id.' recorded.');
    }
}
