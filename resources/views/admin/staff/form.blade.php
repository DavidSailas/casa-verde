@extends('admin.layout')

@php
    $editing = $member !== null;
    $isSelf = $editing && $member->is(auth()->user());
@endphp

@section('title', $editing ? 'Edit '.$member->name : 'Add a person')

@section('content')

    <a href="{{ route('admin.staff.index') }}" class="text-sm font-medium text-forest-700 underline">&larr; All staff</a>
    <h1 class="page-title mt-3">{{ $editing ? 'Edit '.$member->name : 'Add a person' }}</h1>

    <form method="POST" action="{{ $editing ? route('admin.staff.update', $member) : route('admin.staff.store') }}" class="panel mt-8 max-w-2xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div>
            <label class="field-label" for="name">Name</label>
            <input id="name" type="text" name="name" required maxlength="120" value="{{ old('name', $member->name ?? '') }}" class="field-input">
        </div>

        <div>
            <label class="field-label" for="email">E-mail (used to sign in)</label>
            <input id="email" type="email" name="email" required maxlength="150" value="{{ old('email', $member->email ?? '') }}" class="field-input">
        </div>

        <fieldset>
            <legend class="field-label">Role</legend>
            <div class="mt-2 space-y-3">
                @foreach (\App\Models\User::ROLES as $key => $label)
                    <label class="flex items-start gap-3 rounded-lg border border-sand-300 p-3">
                        <input type="radio" name="role" value="{{ $key }}" class="mt-1" @checked(old('role', $editing ? $member->roleKey() : 'editor') === $key) @disabled($isSelf && $key !== 'owner')>
                        <span>
                            <span class="block font-medium text-forest-900">{{ $label }}</span>
                            <span class="block text-sm text-ink-soft">{{ \App\Models\User::ROLE_HELP[$key] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @if ($isSelf)
                <input type="hidden" name="role" value="owner">
                <p class="field-hint">You cannot change your own role. Ask another owner.</p>
            @endif
        </fieldset>

        @if ($editing)
            <div>
                <label class="flex items-center gap-3">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $member->is_admin ? '1' : '0') === '1') @disabled($isSelf)>
                    <span class="field-label">This account can sign in</span>
                </label>
                @if ($isSelf)
                    <input type="hidden" name="active" value="1">
                    <p class="field-hint">You cannot switch off your own account.</p>
                @else
                    <p class="field-hint">Untick to stop this person signing in, for example when they leave. Their account and history stay.</p>
                @endif
            </div>
        @endif

        <div>
            <label class="field-label" for="password">{{ $editing ? 'New password (leave empty to keep the current one)' : 'Password' }}</label>
            <input id="password" type="password" name="password" minlength="10" autocomplete="new-password" @required(! $editing) class="field-input">
            <p class="field-hint">At least 10 characters. {{ $editing ? 'If you set one, the person must choose their own at their next sign-in.' : 'The person will be asked to choose their own at first sign-in. Give them this one in person or by phone, not in a public chat.' }}</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">{{ $editing ? 'Save changes' : 'Add this person' }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

@endsection
