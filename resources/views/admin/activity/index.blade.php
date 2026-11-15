@extends('admin.layout')

@section('title', 'History of changes')

@section('content')

    <h1 class="page-title">History of changes</h1>
    <p class="mt-3 max-w-2xl text-ink-soft">
        Who changed what in this admin, newest first. It records the kind of change and the item, never passwords or what was typed.
    </p>

    <form method="GET" action="{{ route('admin.activity.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="field-label" for="user">Person</label>
            <select id="user" name="user" class="field-input min-w-52" onchange="this.form.submit()">
                <option value="">Everyone</option>
                @foreach ($staff as $person)
                    <option value="{{ $person->id }}" @selected($selected === $person->id)>{{ $person->name }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="btn-secondary">Show</button></noscript>
        @if ($selected) <a href="{{ route('admin.activity.index') }}" class="btn-secondary">Clear</a> @endif
    </form>

    @if ($entries->isEmpty())
        <p class="panel mt-6 text-ink-soft">Nothing has been recorded yet.</p>
    @else
        <ul class="mt-6 divide-y divide-sand-200 rounded-xl border border-sand-300 bg-white">
            @foreach ($entries as $entry)
                <li class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 px-4 py-3">
                    <div class="min-w-0 flex-1 basis-72">
                        <p class="text-[0.9375rem] text-ink">
                            <span class="font-medium text-forest-900">{{ $entry->user_name ?? 'Someone' }}</span>
                            &middot; {{ $entry->action }}
                            @if ($entry->subject) <span class="text-ink-soft">&middot; {{ $entry->subject }}</span> @endif
                            @if ($entry->details) <span class="text-ink-soft">&middot; {{ $entry->details }}</span> @endif
                        </p>
                    </div>
                    <p class="text-xs text-ink-soft" title="{{ $entry->created_at->format('j M Y, g:i:s A') }}{{ $entry->ip ? ' · '.$entry->ip : '' }}">
                        {{ $entry->created_at->diffForHumans() }}
                    </p>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">{{ $entries->links() }}</div>
    @endif

@endsection
