{{--
    A menu page built from the admin: small label + heading, up to four dishes (photo left or right, alternating),
    icon legend and footer. Everything shown here (texts, photos, icons, footer) comes from the admin:
    "Food menu book: cover / categories / dishes / icons".
--}}
@php
    $dishCount = count($page['dishes']);
    $brand = $page['brand'] ?? ['text' => 'Casa Verde Cliff Resort & Spa', 'icon' => ''];
    $logo = trim((string) ($brand['icon'] ?? '')) !== '' ? $brand['icon'] : 'lotus';
    // Long category names ("Burgers and Sandwiches") get a smaller heading so they stay on one line.
    $headingLength = mb_strlen(trim((string) $page['heading']));
    $headingSize = $headingLength > 18 ? 'xl' : ($headingLength > 12 ? 'lg' : '');
@endphp
<div class="mbp" data-count="{{ $dishCount }}">
    @if(trim((string) ($page['kicker'] ?? '')) !== '')
        <p class="mbp-kicker">{{ $page['kicker'] }}</p>
    @endif
    @if(trim((string) $page['heading']) !== '')
        <h3 class="mbp-title{{ $headingSize ? ' mbp-title--'.$headingSize : '' }}">{{ $page['heading'] }}</h3>
    @endif
    <div class="mbp-rule" aria-hidden="true">@include('partials.menu-ornament')</div>

    <div class="mbp-dishes">
        @foreach($page['dishes'] as $dish)
            <article class="mbp-dish">
                <div class="mbp-photo mbp-photo--{{ $dish['shape'] ?? 'round' }}{{ $dish['image'] ? '' : ' mbp-photo--empty' }}">
                    @if($dish['image'])
                        <img src="{{ asset($dish['image']) }}" alt="{{ $dish['name'] }}" loading="lazy" decoding="async" draggable="false">
                    @else
                        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="24" cy="26" r="13"/><circle cx="24" cy="26" r="8.5"/><path d="M8 6v9a3 3 0 0 0 3 3v14M14 6v9a3 3 0 0 1-3 3M40 6c-3 2-4.5 6-4.5 10 0 3 1.5 4.5 4.5 4.5V32"/></svg>
                    @endif
                </div>
                <div class="mbp-text">
                    <h4 class="mbp-name">{{ $dish['name'] }}</h4>
                    @if($dish['price'] !== '' || !empty($dish['icons']))
                        <p class="mbp-meta">
                            @if($dish['price'] !== '')
                                <span class="mbp-price">{{ $dish['price'] }}</span>
                            @endif
                            @if(!empty($dish['icons']))
                                <span class="mbp-diets">
                                    @foreach($dish['icons'] as $icon)
                                        @if($icon['image'])
                                            <img class="mbp-diet mbp-diet--img" src="{{ asset($icon['image']) }}" alt="{{ $icon['label'] }}" draggable="false">
                                        @else
                                            <svg class="mbp-diet" viewBox="0 0 24 24" fill="currentColor" aria-label="{{ $icon['label'] }}" role="img">{!! $icon['svg'] !!}</svg>
                                        @endif
                                    @endforeach
                                </span>
                            @endif
                        </p>
                    @endif
                    @if(trim((string) $dish['text']) !== '')
                        <p class="mbp-desc">{{ $dish['text'] }}</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <ul class="mbp-legend" aria-hidden="true">
        @foreach($page['legend'] ?? [] as $icon)
            <li>
                @if($icon['image'])
                    <img src="{{ asset($icon['image']) }}" alt="" draggable="false">
                @else
                    <svg viewBox="0 0 24 24" fill="currentColor">{!! $icon['svg'] !!}</svg>
                @endif
                <span>{{ $icon['label'] }}</span>
            </li>
        @endforeach
    </ul>
    <div class="mbp-rule mbp-rule--foot" aria-hidden="true">@include('partials.menu-ornament')</div>
    <div class="mbp-brand">
        <x-icon :name="$logo" :size="48" class="mbp-logo" />
        <span>{{ $brand['text'] }}</span>
    </div>
</div>
