@php
    $uid = $item->id ?? 'new';
    $villaValue = $item ? (string) ($item->villa_slug ?? '') : (string) old('villa_slug', '');
    $startValue = $item ? $item->start_date->toDateString() : old('start_date', '');
    $endValue = $item ? $item->end_date->toDateString() : old('end_date', '');
    $reasonValue = $item ? (string) ($item->reason ?? '') : old('reason', '');
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="field-label" for="blk-villa-{{ $uid }}">Villa</label>
        <select id="blk-villa-{{ $uid }}" name="villa_slug" class="field-input">
            <option value="">All villas</option>
            @foreach ($villas as $slug => $title)
                <option value="{{ $slug }}" @selected($villaValue === $slug)>{{ $title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="blk-reason-{{ $uid }}">Reason (only you see this)</label>
        <input id="blk-reason-{{ $uid }}" type="text" name="reason" maxlength="120" value="{{ $reasonValue }}" class="field-input" placeholder="Maintenance, private event...">
    </div>
    <div>
        <label class="field-label" for="blk-start-{{ $uid }}">First closed night</label>
        <input id="blk-start-{{ $uid }}" type="date" name="start_date" required value="{{ $startValue }}" class="field-input">
    </div>
    <div>
        <label class="field-label" for="blk-end-{{ $uid }}">Last closed night</label>
        <input id="blk-end-{{ $uid }}" type="date" name="end_date" required value="{{ $endValue }}" class="field-input">
    </div>
</div>
