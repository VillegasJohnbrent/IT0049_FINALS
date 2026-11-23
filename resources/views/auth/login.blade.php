@extends('layouts.app')
@section('title', 'Staff login')
@section('content')
    <section class="card login-card">
        <p class="eyebrow">STAFF ACCESS</p>
        <h1>Log in</h1>
        <p class="muted">Use your staff email and password.</p>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <x-field name="email" label="Email" type="email" required maxlength="255" autocomplete="username" autofocus />
            <x-field name="password" label="Password" type="password" required maxlength="72" autocomplete="current-password" />
            <button class="button full" type="submit">Log in</button>
        </form>
    </section>
@endsection
