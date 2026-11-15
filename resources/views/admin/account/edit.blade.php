@extends('admin.layout')

@section('title', 'My account')

@section('content')

    <h1 class="page-title">My account</h1>
    <p class="mt-3 max-w-2xl text-ink-soft">
        Your role is <strong>{{ \App\Models\User::ROLES[$user->roleKey()] }}</strong>.
        @if ($user->must_change_password)
            Please choose your own password below before you continue.
        @endif
    </p>

    <form method="POST" action="{{ route('admin.account.update') }}" class="panel mt-8 max-w-2xl space-y-6">
        @csrf @method('PUT')

        <div>
            <label class="field-label" for="name">Name</label>
            <input id="name" type="text" name="name" required maxlength="120" value="{{ old('name', $user->name) }}" class="field-input">
        </div>

        <div>
            <label class="field-label" for="email">E-mail (used to sign in)</label>
            <input id="email" type="email" name="email" required maxlength="150" value="{{ old('email', $user->email) }}" class="field-input">
        </div>

        <div class="border-t border-sand-200 pt-6">
            <h2 class="section-title">Change password</h2>
            <p class="field-hint">{{ $user->must_change_password ? 'Choose a password only you know.' : 'Leave these empty to keep your current password.' }}</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="field-label" for="current_password">Current password</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="field-input">
                </div>
                <div>
                    <label class="field-label" for="password">New password</label>
                    <input id="password" type="password" name="password" minlength="10" autocomplete="new-password" class="field-input">
                    <p class="field-hint">At least 10 characters.</p>
                </div>
                <div>
                    <label class="field-label" for="password_confirmation">New password again</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="field-input">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary">Save</button>
    </form>

@endsection
