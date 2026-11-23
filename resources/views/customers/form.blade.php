@extends('layouts.app')
@section('title', $customer->exists ? 'Edit customer' : 'Add customer')
@section('content')
    <div class="page-heading"><h1>{{ $customer->exists ? 'Edit customer' : 'Add customer' }}</h1></div>
    <form class="card form-card" method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
        @csrf
        @if($customer->exists) @method('PUT') @endif
        <x-field name="full_name" label="Full name" :value="$customer->full_name" required maxlength="100" autocomplete="name" />
        <x-field name="email" label="Email" type="email" :value="$customer->email" required maxlength="255" autocomplete="email" />
        <x-field name="phone" label="Phone (optional)" type="tel" :value="$customer->phone" maxlength="30" autocomplete="tel" />
        <div class="form-actions"><button class="button" type="submit">Save customer</button><a class="button secondary" href="{{ route('customers.index') }}">Cancel</a></div>
    </form>
@endsection
