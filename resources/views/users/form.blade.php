@extends('layouts.app')
@section('title', $staff->exists ? 'Edit staff' : 'Add staff')
@section('content')
    <div class="page-heading"><h1>{{ $staff->exists ? 'Edit staff' : 'Add staff' }}</h1></div>
    <form class="card form-card" method="POST" enctype="multipart/form-data" action="{{ $staff->exists ? route('users.update', $staff) : route('users.store') }}">
        @csrf
        @if($staff->exists) @method('PUT') @endif
        <x-field name="full_name" label="Full name" :value="$staff->full_name" required maxlength="100" autocomplete="name" />
        <x-field name="email" label="Email" type="email" :value="$staff->email" required maxlength="255" autocomplete="username" />
        <div class="form-row">
            <x-field name="password" label="Password" type="password" minlength="8" maxlength="72" :required="! $staff->exists" autocomplete="new-password" :hint="$staff->exists ? 'Leave blank to keep the current password.' : 'Use at least 8 characters.'" />
            <x-field name="password_confirmation" label="Confirm password" type="password" minlength="8" maxlength="72" :required="! $staff->exists" autocomplete="new-password" />
        </div>
        <div class="upload-row">
            <x-photo :path="$staff->avatar" :name="$staff->full_name" :avatar="true" />
            <x-field name="avatar" label="Avatar" type="file" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG, or WebP. Up to 2 MB and 4096 × 4096 pixels." />
        </div>
        <div class="form-actions"><button class="button" type="submit">Save staff</button><a class="button secondary" href="{{ route('users.index') }}">Cancel</a></div>
    </form>
@endsection
