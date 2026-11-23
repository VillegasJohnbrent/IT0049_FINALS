<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('full_name')->orderBy('id')->paginate(12);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['staff' => new User]);
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        Images::save(new User, $data, 'avatar', 'avatars');

        return redirect()->route('users.index')->with('success', 'Staff account added.');
    }

    public function edit(User $user)
    {
        return view('users.form', ['staff' => $user]);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        Images::save($user, $data, 'avatar', 'avatars');

        if ($user->is($request->user()) && isset($data['password'])) {
            $request->user()->refresh();
            $request->session()->put('password_hash_web', $request->user()->getAuthPassword());
            $request->session()->regenerate();
        }

        return redirect()->route('users.index')->with('success', 'Staff account updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => 'You cannot delete the account you are using.']);
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Staff account deleted.');
    }
}
