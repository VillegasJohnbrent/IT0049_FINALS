@extends('layouts.app')
@section('title', 'Sales History')
@section('content')
    <div class="page-heading">
        <div><h1>Sales History</h1><p class="muted">Completed transactions, newest first.</p></div>
        <a class="button" href="{{ route('sales.create') }}">Record sale</a>
    </div>
    <section class="card table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Sale</th><th>Product</th><th>Customer</th><th>Staff</th><th class="number">Qty</th><th class="number">Total</th><th>Date ({{ config('app.timezone') }})</th></tr></thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="muted">#{{ $sale->id }}</td>
                            <td><strong>{{ $sale->product_name }}</strong><small class="block muted">₱{{ number_format($sale->unit_price, 2) }} each</small></td>
                            <td>{{ $sale->customer_name ?: 'Walk-in' }}</td>
                            <td>{{ $sale->staff_name }}</td>
                            <td class="number">{{ $sale->quantity }}</td>
                            <td class="number"><strong>₱{{ number_format($sale->total_price, 2) }}</strong></td>
                            <td class="nowrap">{{ $sale->created_at->format('M d, Y') }}<small class="block muted">{{ $sale->created_at->format('h:i:s A') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No sales recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sales->links() }}
    </section>
@endsection
