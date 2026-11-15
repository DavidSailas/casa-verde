@php
    $uid = $item->id ?? 'new';
    $villaValue = $item ? (string) ($item->villa_slug ?? '') : (string) old('villa_slug', '');
    $nameValue = $item ? (string) $item->name : old('name', '');
    $startValue = $item ? $item->start_date->toDateString() : old('start_date', '');
    $endValue = $item ? $item->end_date->toDateString() : old('end_date', '');
    $priceValue = $item ? rtrim(rtrim(number_format((float) $item->price, 2, '.', ''), '0'), '.') : old('price', '');
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="field-label" for="sea-name-{{ $uid }}">Name of the season</label>
        <input id="sea-name-{{ $uid }}" type="text" name="name" required maxlength="80" value="{{ $nameValue }}" class="field-input" placeholder="High season, Christmas...">
    </div>
    <div>
        <label class="field-label" for="sea-villa-{{ $uid }}">Villa</label>
        <select id="sea-villa-{{ $uid }}" name="villa_slug" class="field-input">
            <option value="">All villas</option>
            @foreach ($villas as $slug => $title)
                <option value="{{ $slug }}" @selected($villaValue === $slug)>{{ $title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="sea-start-{{ $uid }}">First night</label>
        <input id="sea-start-{{ $uid }}" type="date" name="start_date" required value="{{ $startValue }}" class="field-input">
    </div>
    <div>
        <label class="field-label" for="sea-end-{{ $uid }}">Last night</label>
        <input id="sea-end-{{ $uid }}" type="date" name="end_date" required value="{{ $endValue }}" class="field-input">
    </div>
    <div>
        <label class="field-label" for="sea-price-{{ $uid }}">Price per night (PHP)</label>
        <input id="sea-price-{{ $uid }}" type="number" name="price" required min="1" step="0.01" inputmode="decimal" value="{{ $priceValue }}" class="field-input">
    </div>
</div>
