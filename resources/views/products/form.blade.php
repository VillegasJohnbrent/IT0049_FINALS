@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
    <div class="page-heading"><h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1></div>
    <form class="card form-card" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
        @csrf
        @if($product->exists)
            @method('PUT')
            <input type="hidden" name="original_stock_quantity" value="{{ old('original_stock_quantity', $product->stock_quantity) }}">
        @endif
        <x-field name="name" label="Product name" :value="$product->name" required maxlength="150" />
        <div class="form-row">
            <x-field name="price" label="Price (₱)" type="number" :value="$product->price" min="0.01" max="999999.99" step="0.01" required />
            <x-field name="stock_quantity" label="Stock quantity" type="number" :value="$product->stock_quantity ?? 0" min="0" max="1000000" step="1" required />
        </div>
        <div class="upload-row">
            <x-photo :path="$product->image" :name="$product->name" />
            <x-field name="image" label="Product image" type="file" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG, or WebP. Up to 2 MB and 4096 × 4096 pixels. Leave blank to keep the current image." />
        </div>
        <div class="form-actions"><button class="button" type="submit">Save product</button><a class="button secondary" href="{{ route('products.index') }}">Cancel</a></div>
    </form>
@endsection
