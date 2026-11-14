{{--
    Food menu: a full-screen menu opened by the EXPLORE FOOD MENU button on the Dining page.
    Category tabs stay on top, every dish is a card (photo, name, price, description, ingredient tags),
    and guests can filter by ingredient. Everything comes from the admin ("Food menu book: categories / dishes / icons").

    Needs: $id (dom id), $title (name of the menu) and $pages (from App\Support\FoodMenu::pages()).
--}}
@php
    use Illuminate\Support\Str;

    $dishPages = collect($pages)->where('type', 'dishes');
    $brand = $dishPages->first()['brand'] ?? ['text' => 'Casa Verde Cliff Resort & Spa', 'icon' => ''];
    $logo = trim((string) ($brand['icon'] ?? '')) !== '' ? $brand['icon'] : 'lotus';

    // One section per category, in the admin's order (the book's pages are merged back together).
    $sections = $dishPages->groupBy('heading')->map(fn ($group, $heading) => [
        'heading' => $heading,
        'slug' => Str::slug($heading) ?: 'section',
        'dishes' => $group->pluck('dishes')->flatten(1)->all(),
    ])->values();

    // Filter chips: only ingredients that at least one dish uses, in the admin's icon order.
    $usedTags = $sections->pluck('dishes')->flatten(1)->pluck('icons')->flatten(1)->pluck('label')->unique();
    $filters = collect($dishPages->first()['legend'] ?? [])->filter(fn ($icon) => $usedTags->contains($icon['label']))->values();
    $dishTotal = $sections->sum(fn ($section) => count($section['dishes']));
@endphp

<dialog class="fm" id="{{ $id }}" data-fm aria-labelledby="{{ $id }}-title">
    <div class="fm-scroll" data-fm-scroll>

        <header class="fm-bar" data-fm-bar>
            <div class="fm-bar-inner">
                <div class="fm-brand">
                    <x-icon :name="$logo" :size="40" class="fm-logo" />
                    <div>
                        <h2 class="fm-title" id="{{ $id }}-title">{{ $title }}</h2>
                        <p class="fm-sub">{{ $brand['text'] }}</p>
                    </div>
                </div>
                <button type="button" class="fm-close" data-fm-close aria-label="Close menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            @if($sections->count() > 1)
                <nav class="fm-tabs" aria-label="Menu categories" data-fm-tabs>
                    @foreach($sections as $section)
                        <a href="#{{ $id }}-{{ $section['slug'] }}" class="fm-tab{{ $loop->first ? ' is-active' : '' }}" data-fm-tab="{{ $section['slug'] }}">{{ $section['heading'] }}</a>
                    @endforeach
                </nav>
            @endif
        </header>

        <div class="fm-body">

            @if($filters->isNotEmpty())
                <div class="fm-filters" role="group" aria-label="Filter dishes by ingredient">
                    <span class="fm-filters-label">Filter by</span>
                    <button type="button" class="fm-chip is-on" data-fm-filter="" aria-pressed="true">All dishes</button>
                    @foreach($filters as $icon)
                        <button type="button" class="fm-chip" data-fm-filter="{{ Str::slug($icon['label']) }}" aria-pressed="false">
                            @if($icon['image'])
                                <img src="{{ asset($icon['image']) }}" alt="" draggable="false">
                            @else
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $icon['svg'] !!}</svg>
                            @endif
                            {{ $icon['label'] }}
                        </button>
                    @endforeach
                    <span class="fm-found" aria-live="polite" data-fm-found></span>
                </div>
            @endif

            @foreach($sections as $section)
                <section class="fm-section" id="{{ $id }}-{{ $section['slug'] }}" data-fm-section="{{ $section['slug'] }}" aria-labelledby="{{ $id }}-{{ $section['slug'] }}-h">
                    <div class="fm-section-head">
                        <h3 id="{{ $id }}-{{ $section['slug'] }}-h">{{ $section['heading'] }}</h3>
                        <span class="fm-rule" aria-hidden="true"></span>
                    </div>

                    <div class="fm-grid">
                        @foreach($section['dishes'] as $dish)
                            <article class="fm-dish" data-tags="{{ collect($dish['icons'])->map(fn ($i) => Str::slug($i['label']))->implode(' ') }}">
                                <div class="fm-photo">
                                    @if($dish['image'])
                                        <img src="{{ asset($dish['image']) }}" alt="{{ $dish['name'] }}" loading="lazy" decoding="async" draggable="false">
                                    @endif
                                </div>
                                <div class="fm-info">
                                    <div class="fm-line">
                                        <h4 class="fm-name">{{ $dish['name'] }}</h4>
                                        @if($dish['price'] !== '')
                                            <p class="fm-price"><span>PHP</span> {{ $dish['price'] }}</p>
                                        @endif
                                    </div>
                                    @if(trim((string) $dish['text']) !== '')
                                        <p class="fm-desc">{{ $dish['text'] }}</p>
                                    @endif
                                    @if(!empty($dish['icons']))
                                        <ul class="fm-tags" aria-label="Contains">
                                            @foreach($dish['icons'] as $icon)
                                                <li>
                                                    @if($icon['image'])
                                                        <img src="{{ asset($icon['image']) }}" alt="" draggable="false">
                                                    @else
                                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $icon['svg'] !!}</svg>
                                                    @endif
                                                    {{ $icon['label'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <p class="fm-empty" data-fm-empty hidden>
                No dishes match this filter.
                <button type="button" data-fm-filter="">Show all dishes</button>
            </p>

            <footer class="fm-foot">
                <p>{{ $dishTotal }} dishes &middot; Prices are in Philippine pesos (PHP).</p>
            </footer>
        </div>

    </div>
</dialog>
