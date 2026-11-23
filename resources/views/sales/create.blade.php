@extends('layouts.app')
@section('title', 'Record Sale')
@section('content')
    <div class="page-heading"><div><h1>Record Sale</h1><p class="muted">Choose a product and enter the quantity sold.</p></div></div>
    @if($products->isEmpty())
        <div class="card empty">Add a product before recording a sale. <a href="{{ route('products.create') }}">Add product</a></div>
    @else
        <form method="POST" action="{{ route('sales.store') }}" class="sale-layout" id="sale-form">
            @csrf
            <input type="hidden" name="reference" value="{{ old('reference', $reference) }}">
            <section class="card">
                <h2>Sale details</h2>
                <div class="field">
                    <label for="product_id">Product</label>
                    <select name="product_id" id="product_id" required>
                        <option value="">Select a product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" data-name="{{ $product->name }}" data-image="{{ $product->image ? Storage::disk('public')->url($product->image) : '' }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }} — ₱{{ number_format($product->price, 2) }} ({{ $product->stock_quantity }} in stock)</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="customer_id">Customer (optional)</label>
                    <select name="customer_id" id="customer_id">
                        <option value="">Walk-in customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->full_name }} — {{ $customer->email }}</option>
                        @endforeach
                    </select>
                </div>
                <x-field name="quantity" label="Quantity" type="number" value="1" min="1" max="1000000" step="1" required />
                <p class="muted small-text">Recorded by {{ auth()->user()->full_name }}.</p>
            </section>
            <aside class="card sale-summary" aria-label="Sale total">
                <h2>Summary</h2>
                <img id="sale-image" class="sale-image" alt="" hidden>
                <p id="sale-product" class="summary-product">No product selected</p>
                <dl>
                    <div><dt>Unit price</dt><dd id="unit-price">—</dd></div>
                    <div><dt>Available stock</dt><dd id="available-stock">—</dd></div>
                    <div><dt>Quantity</dt><dd id="sale-quantity">—</dd></div>
                </dl>
                <div class="total-row"><span>Total</span><strong id="sale-total" aria-live="polite">₱0.00</strong></div>
                <p id="stock-warning" class="field-error" role="status" hidden>Quantity exceeds available stock.</p>
                <button class="button full" type="submit">Record sale</button>
                <p class="muted small-text">Stock is deducted when the sale is saved.</p>
                <noscript><p>The total will be calculated when you save the sale.</p></noscript>
            </aside>
        </form>
    @endif
@endsection
