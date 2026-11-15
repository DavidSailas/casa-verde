@extends('admin.layout')

@section('title', 'Availability')

@php
    $cellStyle = [
        'free' => 'bg-white text-ink-soft/40',
        'partly' => 'bg-gold-300 text-forest-900',
        'full' => 'bg-danger-50 text-danger-700',
        'closed' => 'bg-sand-300 text-ink-soft',
    ];
    $cellName = ['free' => 'Free', 'partly' => 'Partly booked', 'full' => 'Fully booked', 'closed' => 'Closed'];
@endphp

@section('content')

    <div>
        <h1 class="page-title">Availability</h1>
        <p class="mt-3 max-w-3xl text-ink-soft">
            See which nights are taken, close dates by hand, set seasonal prices and say how many villas you have of each type.
            A night is taken when you close it here or when confirmed bookings use every villa of that type. Requests still waiting for a reply do not hold dates,
            and a booking cannot be confirmed if it would double-book a night.
        </p>
    </div>

    {{-- ===== Month calendar ===== --}}
    <section class="mt-10" aria-labelledby="cal-title">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="cal-title" class="section-title">{{ $month->format('F Y') }}</h2>
            <nav class="flex gap-2" aria-label="Choose a month">
                <a href="{{ route('admin.availability.index', ['month' => $previous]) }}" class="btn-secondary">&larr; Previous</a>
                <a href="{{ route('admin.availability.index') }}" class="btn-secondary">This month</a>
                <a href="{{ route('admin.availability.index', ['month' => $next]) }}" class="btn-secondary">Next &rarr;</a>
            </nav>
        </div>

        @if ($calendar === [])
            <p class="panel mt-4 text-ink-soft">No villa is open for online booking yet. Add villas and their rates under Site content, then come back.</p>
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-sand-300 bg-white">
                <table class="min-w-full border-collapse text-xs">
                    <thead>
                        <tr>
                            <th scope="col" class="sticky left-0 z-10 min-w-40 bg-white px-3 py-2 text-left font-medium text-forest-900">Villa</th>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php($date = $month->copy()->day($day))
                                <th scope="col" class="min-w-8 border-l border-sand-200 px-0.5 py-1 text-center font-medium {{ $date->isWeekend() ? 'bg-sand-100' : '' }}">
                                    <span class="block text-ink-soft">{{ $date->format('D')[0] }}</span>
                                    <span class="block text-forest-900">{{ $day }}</span>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($calendar as $slug => $row)
                            <tr class="border-t border-sand-200">
                                <th scope="row" class="sticky left-0 z-10 bg-white px-3 py-2 text-left font-medium text-forest-900">
                                    {{ $row['title'] }}
                                    <span class="block text-[0.6875rem] font-normal text-ink-soft">{{ $row['units'] }} {{ $row['units'] === 1 ? 'villa' : 'villas' }}</span>
                                </th>
                                @foreach ($row['days'] as $day => $cell)
                                    <td class="border-l border-sand-200 p-0 text-center">
                                        <span class="block h-9 min-w-8 leading-9 {{ $cellStyle[$cell['state']] }}"
                                              title="{{ $row['title'] }}, {{ $month->copy()->day($day)->format('j M') }}: {{ $cellName[$cell['state']] }}{{ $cell['booked'] > 0 ? ' ('.$cell['booked'].' booked)' : '' }}">
                                            @if ($cell['state'] === 'closed') &times; @elseif ($cell['booked'] > 0) {{ $cell['booked'] }} @endif
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-ink-soft" aria-label="Colour key">
                @foreach ($cellName as $state => $name)
                    <li class="flex items-center gap-2"><span class="inline-block h-4 w-4 rounded border border-sand-300 {{ $cellStyle[$state] }}"></span>{{ $name }}</li>
                @endforeach
            </ul>
            <p class="mt-2 text-sm text-ink-soft">The number in a square is how many confirmed bookings use that night. Each column is a night, from its date until the next morning.</p>
        @endif
    </section>

    {{-- ===== Closed dates ===== --}}
    <section class="mt-14" aria-labelledby="closed-title">
        <h2 id="closed-title" class="section-title">Closed dates</h2>
        <p class="mt-2 max-w-3xl text-ink-soft">Nights nobody can book, for maintenance, a private event or the owner's own use. Guests see these dates as not available.</p>

        <form method="POST" action="{{ route('admin.availability.blocks.store') }}" class="panel mt-4">
            @csrf
            <h3 class="font-medium text-forest-900">Close dates</h3>
            <div class="mt-4">@include('admin.availability._block_fields', ['item' => null])</div>
            <button type="submit" class="btn-primary mt-5">Close these dates</button>
        </form>

        @if ($blocks->isNotEmpty())
            <ul class="mt-4 divide-y divide-sand-200 rounded-xl border border-sand-300 bg-white">
                @foreach ($blocks as $block)
                    <li class="px-4 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-forest-900">
                                    {{ $block->start_date->format('j M Y') }}
                                    @if (! $block->start_date->isSameDay($block->end_date)) &ndash; {{ $block->end_date->format('j M Y') }} @endif
                                </p>
                                <p class="text-sm text-ink-soft">
                                    {{ $block->villa_slug ? ($villas[$block->villa_slug] ?? $block->villa_slug) : 'All villas' }}@if ($block->reason) &middot; {{ $block->reason }}@endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.availability.blocks.destroy', $block) }}" onsubmit="return confirm('Open these dates for booking again?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-quiet-danger">Remove</button>
                            </form>
                        </div>
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm font-medium text-forest-700">Change these dates</summary>
                            <form method="POST" action="{{ route('admin.availability.blocks.update', $block) }}" class="mt-4">
                                @csrf @method('PUT')
                                @include('admin.availability._block_fields', ['item' => $block])
                                <button type="submit" class="btn-secondary mt-4">Save changes</button>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-4 text-sm text-ink-soft">No dates are closed at the moment.</p>
        @endif
    </section>

    {{-- ===== Seasonal rates ===== --}}
    <section class="mt-14" aria-labelledby="season-title">
        <h2 id="season-title" class="section-title">Seasonal rates</h2>
        <p class="mt-2 max-w-3xl text-ink-soft">
            A different price per night for a period, such as high season or Christmas. It replaces the regular price on those nights.
            A promo price set under Booking page: rates &amp; terms still wins on the nights it covers. If two seasons overlap, the one for a single villa wins over the one for all villas, then the one that starts later.
        </p>

        <form method="POST" action="{{ route('admin.availability.seasons.store') }}" class="panel mt-4">
            @csrf
            <h3 class="font-medium text-forest-900">Add a seasonal rate</h3>
            <div class="mt-4">@include('admin.availability._season_fields', ['item' => null])</div>
            <button type="submit" class="btn-primary mt-5">Add seasonal rate</button>
        </form>

        @if ($seasons->isNotEmpty())
            <ul class="mt-4 divide-y divide-sand-200 rounded-xl border border-sand-300 bg-white">
                @foreach ($seasons as $season)
                    <li class="px-4 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-forest-900">{{ $season->name }} &middot; &#8369;{{ number_format((float) $season->price, 2) }} per night</p>
                                <p class="text-sm text-ink-soft">
                                    {{ $season->start_date->format('j M Y') }} &ndash; {{ $season->end_date->format('j M Y') }}
                                    &middot; {{ $season->villa_slug ? ($villas[$season->villa_slug] ?? $season->villa_slug) : 'All villas' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.availability.seasons.destroy', $season) }}" onsubmit="return confirm('Delete this seasonal rate?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-quiet-danger">Delete</button>
                            </form>
                        </div>
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm font-medium text-forest-700">Change this rate</summary>
                            <form method="POST" action="{{ route('admin.availability.seasons.update', $season) }}" class="mt-4">
                                @csrf @method('PUT')
                                @include('admin.availability._season_fields', ['item' => $season])
                                <button type="submit" class="btn-secondary mt-4">Save changes</button>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-4 text-sm text-ink-soft">No seasonal rates yet. Every night uses the regular or promo price.</p>
        @endif
    </section>

    {{-- ===== Villas per type ===== --}}
    @if ($villas !== [])
        <section class="mt-14" aria-labelledby="units-title">
            <h2 id="units-title" class="section-title">How many villas of each type</h2>
            <p class="mt-2 max-w-3xl text-ink-soft">Two guests can stay on the same night only if you have more than one villa of that type.</p>

            <form method="POST" action="{{ route('admin.availability.units.update') }}" class="panel mt-4">
                @csrf @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($villas as $slug => $title)
                        <div>
                            <label class="field-label" for="units-{{ $slug }}">{{ $title }}</label>
                            <input id="units-{{ $slug }}" type="number" name="units[{{ $slug }}]" min="1" max="99" required
                                   value="{{ old("units.{$slug}", $calendar[$slug]['units'] ?? 1) }}" class="field-input">
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn-primary mt-5">Save</button>
            </form>
        </section>
    @endif

@endsection
