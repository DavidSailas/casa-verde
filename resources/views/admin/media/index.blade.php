@extends('admin.layout')

@section('title', 'Media library')

@php
    $chip = fn (bool $active) => 'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors '
        .($active ? 'border-forest-900 bg-forest-900 text-sand-50' : 'border-sand-300 bg-white text-forest-900 hover:border-forest-700');

    $badge = [
        'in_use' => 'bg-forest-50 text-forest-800',
        'default' => 'bg-gold-400/20 text-gold-600',
        'hidden' => 'bg-sand-200 text-ink-soft',
        'unused' => 'bg-danger-50 text-danger-600',
    ];
@endphp

@section('content')

    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="page-title">Media library</h1>
            <p class="mt-3 max-w-2xl text-ink-soft">
                Every photo, icon, symbol and logo on the site, newest first. The label on each picture tells you whether the live site is using it right now.
            </p>
        </div>
        <a href="{{ route('admin.content.index') }}" class="btn-secondary self-start whitespace-nowrap sm:self-auto">Edit site content</a>
    </div>

    @include('admin.media._webp')

    @include('admin.media._cleanup')

    <style>
    .mf-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px; border: 1px solid #dcd0bc; border-radius: 14px; background: #fff; }
    .mf-hidden { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }
    .mf-search { position: relative; display: block; flex: 1 1 260px; max-width: 420px; }
    .mf-search svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #52625c; pointer-events: none; }
    .mf-search input, .mf-select select { display: block; width: 100%; height: 44px; margin: 0; border: 1px solid #dcd0bc; border-radius: 10px; background-color: #fcfaf6; color: #12241f; font-size: .94rem; transition: border-color .2s, box-shadow .2s, background-color .2s; }
    .mf-search input { padding: 0 14px 0 42px; }
    .mf-search input::placeholder { color: #7d8b85; }
    .mf-select { position: relative; display: block; flex: 0 0 auto; }
    .mf-select select { appearance: none; -webkit-appearance: none; min-width: 168px; padding: 0 40px 0 14px; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2352625c' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-size: 18px; }
    .mf-search input:hover, .mf-select select:hover { border-color: #b9ab92; }
    .mf-search input:focus, .mf-select select:focus { outline: 0; border-color: #1c5646; background-color: #fff; box-shadow: 0 0 0 4px rgba(201, 160, 106, .25); }
    @media (max-width: 640px) { .mf-search { flex-basis: 100%; max-width: none; } .mf-select { flex: 1 1 auto; } .mf-select select { min-width: 0; } }
    </style>

    {{-- Filters --}}
    <div class="mt-8 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-sm font-medium text-forest-900">Type</span>
            <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="{{ $chip(! $type) }}">All <span class="opacity-70">{{ $total }}</span></a>
            @foreach ($types as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['type' => $key]) }}" class="{{ $chip($type === $key) }}">{{ $label }} <span class="opacity-70">{{ $typeCounts[$key] ?? 0 }}</span></a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-sm font-medium text-forest-900">Status</span>
            <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="{{ $chip(! $status) }}">All</a>
            @foreach ($statuses as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}" class="{{ $chip($status === $key) }}">{{ $label }} <span class="opacity-70">{{ $statusCounts[$key] ?? 0 }}</span></a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.media.index') }}" class="mf-bar">
            @if ($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif

            <label class="mf-search">
                <span class="mf-hidden">Search by file name</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search by file name">
            </label>

            <label class="mf-select">
                <span class="mf-hidden">Sort</span>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" @selected($sort === 'newest')>Newest first</option>
                    <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                    <option value="name" @selected($sort === 'name')>File name</option>
                </select>
            </label>

            <button type="submit" class="mc-btn mc-btn--primary">Apply</button>

            @if ($search !== '' || $sort !== 'newest')
                <a href="{{ request()->fullUrlWithQuery(['q' => null, 'sort' => null]) }}" class="mc-btn mc-btn--ghost">Clear</a>
            @endif
        </form>
    </div>

    <p class="mt-6 text-sm text-ink-soft">
        Showing {{ $items->count() }} of {{ $total }} {{ \Illuminate\Support\Str::plural('file', $total) }}.
        “In use now” covers pictures in active admin entries and pictures built into the page design. Symbols the code picks by name may not be detected.
    </p>

    @if ($items->isEmpty())
        <div class="mt-4 rounded-xl border border-dashed border-sand-300 bg-white px-6 py-14 text-center">
            <p class="font-medium text-forest-900">No pictures match</p>
            <p class="mt-1 text-ink-soft">Try another type or status.</p>
        </div>
    @else
        <ul role="list" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($items as $file)
                <li class="flex flex-col overflow-hidden rounded-xl border border-sand-300 bg-white" data-media-card>
                    <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener"
                       class="block h-44 border-b border-sand-200 p-3"
                       style="background-image:conic-gradient(#efeae0 25%,#fff 0 50%,#efeae0 0 75%,#fff 0);background-size:16px 16px"
                       title="Open full size">
                        <img src="{{ asset($file['path']) }}" alt="{{ $file['name'] }}" loading="lazy" decoding="async" class="h-full w-full object-contain">
                    </a>

                    <div class="flex flex-1 flex-col gap-2 p-4">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge[$file['status']] }}">{{ $statuses[$file['status']] }}</span>
                            <span class="rounded-full bg-sand-100 px-2.5 py-0.5 text-xs text-ink-soft">{{ rtrim($types[$file['type']], 's') }}</span>
                            @if ($file['is_new'])
                                <span class="rounded-full bg-gold-400 px-2.5 py-0.5 text-xs font-semibold text-forest-900">New this week</span>
                            @endif
                            @if ($file['newer'])
                                <span class="rounded-full bg-sand-200 px-2.5 py-0.5 text-xs text-ink-soft" title="{{ $file['versions'] }} uploads of this picture; a newer one exists">Older version</span>
                            @elseif ($file['versions'] > 1)
                                <span class="rounded-full bg-forest-50 px-2.5 py-0.5 text-xs text-forest-800" title="{{ $file['versions'] }} uploads of this picture; this is the newest">Latest version</span>
                            @endif
                        </div>

                        <p class="select-all break-all text-sm font-medium text-forest-900">{{ $file['path'] }}</p>

                        <p class="text-xs text-ink-soft">
                            Uploaded {{ $file['uploaded']->format('M j, Y · g:i A') }}<br>
                            @if ($file['width']) {{ $file['width'] }} × {{ $file['height'] }} px · @endif
                            {{ \Illuminate\Support\Number::fileSize($file['bytes']) }} · {{ strtoupper($file['extension']) }}
                        </p>

                        @php($usedCount = count($file['admin_uses']) + count($file['code_uses']) + count($file['default_uses']))
                        @if ($usedCount > 0)
                            <details class="mt-auto pt-1 text-sm">
                                <summary class="cursor-pointer text-forest-800 underline-offset-2 hover:underline">Where it is used ({{ $usedCount }})</summary>
                                <ul class="mt-2 space-y-1 text-ink-soft">
                                    @foreach ($file['admin_uses'] as $use)
                                        <li>
                                            <a href="{{ route('admin.content.edit', $use['id']) }}" class="text-forest-800 underline-offset-2 hover:underline">{{ $use['section'] }} · {{ \Illuminate\Support\Str::limit($use['title'], 40) }}</a>
                                            <span class="text-xs">({{ $use['field'] }}@if (! $use['active']), hidden @endif)</span>
                                        </li>
                                    @endforeach
                                    @foreach ($file['code_uses'] as $use)
                                        <li><span class="text-xs">Built into the design: {{ $use }}</span></li>
                                    @endforeach
                                    @foreach ($file['default_uses'] as $use)
                                        <li><span class="text-xs">Starting design: {{ $use }}</span></li>
                                    @endforeach
                                </ul>
                            </details>
                        @else
                            <p class="mt-auto pt-1 text-sm text-ink-soft">Nothing on the site uses this file.</p>
                        @endif

                        <div class="mc-pic-actions">
                            <a href="{{ asset($file['path']) }}" target="_blank" rel="noopener" class="mc-link">Open full size</a>

                            @if ($file['status'] === 'unused')
                                <button type="button" class="mc-trash"
                                        data-trash-path="{{ $file['path'] }}"
                                        data-trash-src="{{ asset($file['path']) }}"
                                        data-trash-risky="{{ in_array($file['type'], ['icon', 'symbol'], true) ? '1' : '0' }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12.2a1.8 1.8 0 0 0 1.8 1.8h6.4a1.8 1.8 0 0 0 1.8-1.8L18 7M9 7V4.8A.8.8 0 0 1 9.8 4h4.4a.8.8 0 0 1 .8.8V7"/></svg>
                                    Move to trash
                                </button>
                            @else
                                <span class="mc-pic-note">In use, can't be removed</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

@endsection
