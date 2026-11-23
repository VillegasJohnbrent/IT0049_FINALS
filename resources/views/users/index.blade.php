@extends('layouts.app')
@section('title', 'Staff')
@section('content')
    <div class="page-heading">
        <div><h1>Staff</h1><p class="muted">Staff accounts can manage records and record sales.</p></div>
        <a class="button" href="{{ route('users.create') }}">Add staff</a>
    </div>
    <section class="card table-card">
        <div class="table-scroll">
            <table>
                <thead><tr><th>Full name</th><th>Email</th><th>Added</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($users as $staff)
                        <tr>
                            <td><div class="item"><x-photo :path="$staff->avatar" :name="$staff->full_name" :avatar="true" /><strong>{{ $staff->full_name }} @if($staff->is(auth()->user())) <span class="badge">You</span> @endif</strong></div></td>
                            <td>{{ $staff->email }}</td><td>{{ $staff->created_at->format('M d, Y') }}</td>
                            <td><div class="actions">
                                <a class="button secondary small" href="{{ route('users.edit', $staff) }}">Edit</a>
                                @unless($staff->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.destroy', $staff) }}" data-confirm="Delete this staff account? Access will be removed. Past sales will remain.">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form>
                                @endunless
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No staff accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </section>
@endsection
