<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** "My account": every signed-in person can change their own name, e-mail and password. */
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.account.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
        ], [
            'current_password.required_with' => 'Enter your current password to choose a new one.',
            'current_password.current_password' => 'Your current password is not correct.',
            'password.min' => 'The new password must be at least 10 characters.',
            'password.confirmed' => 'The two new passwords do not match.',
        ]);

        // Someone who was told to choose a password has to actually choose one.
        if ($user->must_change_password && blank($data['password'] ?? null)) {
            return back()->withErrors(['password' => 'Please choose a new password to continue.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password']; // hashed by the model
            $user->must_change_password = false;
        }

        $user->save();

        return redirect()->route('admin.account.edit')->with('status', 'Your account was saved.');
    }
}
