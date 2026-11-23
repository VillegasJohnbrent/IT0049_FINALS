@extends('layouts.app')
@section('title', 'Customers')
@section('content')
    <div class="page-heading">
        <div><h1>Customers</h1><p class="muted">Customer details for sales records.</p></div>
        <a class="button" href="{{ route('customers.create') }}">Add customer</a>
    </div>
    <section class="card table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Full name</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->full_name }}</strong></td><td>{{ $customer->email }}</td><td>{{ $customer->phone ?: '—' }}</td>
                            <td><div class="actions">
                                <a class="button secondary small" href="{{ route('customers.edit', $customer) }}">Edit</a>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="Delete this customer? Past sales will remain.">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No customers yet. Sales can also be recorded for walk-in customers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </section>
@endsection
