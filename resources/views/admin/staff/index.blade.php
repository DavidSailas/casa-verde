@extends('admin.layout')

@section('title', 'Staff')

@section('content')

    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="page-title">Staff</h1>
            <p class="mt-3 max-w-2xl text-ink-soft">
                Everyone who can sign in to this admin. Choose what each person may open, switch an account off when someone leaves, or set a new password if it is forgotten.
            </p>
        </div>
        <a href="{{ route('admin.staff.create') }}" class="btn-primary whitespace-nowrap">Add a person</a>
    </div>

    <ul class="mt-8 divide-y divide-sand-200 rounded-xl border border-sand-300 bg-white">
        @foreach ($staff as $member)
            <li class="px-4 py-4">
                <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                    <div class="min-w-0 flex-1 basis-64">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <span class="font-medium text-forest-900">{{ $member->name }}</span>
                            @if ($member->is(auth()->user())) <span class="text-xs text-ink-soft">(you)</span> @endif
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $member->is_admin ? 'bg-forest-100 text-forest-800' : 'bg-sand-200 text-ink-soft' }}">
                                {{ $member->is_admin ? \App\Models\User::ROLES[$member->roleKey()] : 'Switched off' }}
                            </span>
                            @if ($member->must_change_password)
                                <span class="rounded-full bg-gold-300 px-2 py-0.5 text-xs font-medium text-forest-900" title="They will choose their own password at their next sign-in">New password needed</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-ink-soft">
                            {{ $member->email }}
                            &middot; {{ $member->last_login_at ? 'Last signed in '.$member->last_login_at->diffForHumans() : 'Has not signed in yet' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.staff.edit', $member) }}" class="btn-secondary">Edit</a>
                        @unless ($member->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Delete this account? Their past changes stay in the history.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-quiet-danger">Delete</button>
                            </form>
                        @endunless
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    <section class="panel mt-10" aria-labelledby="roles-title">
        <h2 id="roles-title" class="section-title">What each role can do</h2>
        <dl class="mt-4 space-y-3 text-[0.9375rem]">
            @foreach (\App\Models\User::ROLES as $key => $label)
                <div>
                    <dt class="font-medium text-forest-900">{{ $label }}</dt>
                    <dd class="text-ink-soft">{{ \App\Models\User::ROLE_HELP[$key] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

@endsection
