@extends('layouts.app')
@section('title', 'Products')
@section('content')
    <div class="page-heading">
        <div><h1>{{ $archived ? 'Archived products' : 'Products' }}</h1><p class="muted">Manage prices, stock, and product images.</p></div>
        <a class="button" href="{{ route('products.create') }}">Add product</a>
    </div>
    <div class="tabs">
        <a href="{{ route('products.index') }}" @class(['selected' => ! $archived])>Active products</a>
        <a href="{{ route('products.index', ['archived' => 1]) }}" @class(['selected' => $archived])>Archived</a>
    </div>
    <section class="card table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Product</th><th class="number">Price</th><th class="number">Stock</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><div class="item"><x-photo :path="$product->image" :name="$product->name" /><strong>{{ $product->name }}</strong></div></td>
                            <td class="number">₱{{ number_format($product->price, 2) }}</td>
                            <td class="number"><span @class(['badge', 'warning' => $product->stock_quantity === 0])>{{ $product->stock_quantity }}</span></td>
                            <td><div class="actions">
                                @if($archived)
                                    <form method="POST" action="{{ route('products.restore', $product) }}">@csrf @method('PATCH')<button class="button secondary small" type="submit">Restore</button></form>
                                @else
                                    <a class="button secondary small" href="{{ route('products.edit', $product) }}">Edit</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Archive this product? It will be removed from new sales.">@csrf @method('DELETE')<button class="button danger small" type="submit">Archive</button></form>
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">{{ $archived ? 'No archived products.' : 'No products yet. Add a product to start recording sales.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </section>
@endsection
