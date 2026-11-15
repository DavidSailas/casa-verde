<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Staff accounts (owners only): add people, choose what they may open, switch them off, reset a password. */
class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => User::orderByDesc('is_admin')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.form', ['member' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'string', 'min:10'],
        ], [
            'password.min' => 'The password must be at least 10 characters.',
        ]);

        $member = User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_admin' => true,
            'must_change_password' => true, // they choose their own at first sign-in
        ]);

        return redirect()->route('admin.staff.index')
            ->with('status', "{$member->name} can now sign in with the password you set. They will be asked to choose their own.");
    }

    public function edit(User $user): View
    {
        return view('admin.staff.form', ['member' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'string', 'min:10'],
        ], [
            'password.min' => 'The password must be at least 10 characters.',
        ]);

        $active = $request->boolean('active');
        $isSelf = $user->is(auth()->user());

        // Nobody can lock themselves out, and the site must always keep an owner.
        if ($isSelf && (! $active || $data['role'] !== 'owner')) {
            return back()->withInput()->withErrors(['role' => 'You cannot switch off or demote your own account. Ask another owner to do it.']);
        }

        if ($user->isOwner() && (! $active || $data['role'] !== 'owner') && $this->otherOwners($user) === 0) {
            return back()->withInput()->withErrors(['role' => 'There must always be at least one active owner.']);
        }

        $user->fill([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'role' => $data['role'],
            'is_admin' => $active,
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password']; // hashed by the model
            $user->must_change_password = true;
        }

        $user->save();

        return redirect()->route('admin.staff.index')->with('status', "{$user->name} was saved.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['staff' => 'You cannot delete your own account.']);
        }

        if ($user->isOwner() && $this->otherOwners($user) === 0) {
            return back()->withErrors(['staff' => 'There must always be at least one active owner.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.staff.index')->with('status', "{$name} was deleted. Their past changes stay in the history.");
    }

    /** How many active owners there are besides this person. */
    protected function otherOwners(User $user): int
    {
        return User::where('is_admin', true)
            ->where('id', '!=', $user->id)
            ->where(fn ($q) => $q->whereNull('role')->orWhere('role', 'owner'))
            ->count();
    }
}
