{{--
    Drinks menu: a full-screen menu opened by the EXPLORE DRINKS MENU button on the Dining page.
    The header (title, close button, category tabs) stays fixed; only the menu below it scrolls, so the
    scrollbar never runs down the dark header. Filters stay in reach while scrolling on larger screens.
    Drinks are photo cards or a compact list (per category). Everything comes from the admin
    (Dining > Drinks menu), built by App\Support\DrinksMenu.

    Needs: $id (dom id of the dialog), $menu (the array returned by DrinksMenu::data()).
--}}
@php
    use Illuminate\Support\Str;

    $categories = collect($menu['categories']);
    $title = $menu['title'];
    $currency = $menu['currency'];
    $tagSlugs = fn (array $item) => collect($item['tags'] ?? [])->map(fn ($tag) => Str::slug($tag))->implode(' ');
    $filters = collect($menu['filters']);
    $total = $menu['total'];

    // Drawn on drinks without a photo, chosen from the drink's tags so each card still looks intentional.
    $glasses = [
        'cocktail' => '<path d="M10 10h28L24 26 10 10Z"/><path d="M24 26v14M16 40h16"/><circle cx="33" cy="9" r="3"/>',
        'wine' => '<path d="M16 8h16c0 9-2 15-8 16-6-1-8-7-8-16Z"/><path d="M17.5 15h13M24 24v14M17 40h14"/>',
        'tall' => '<path d="M15 12h18l-2.5 26a2 2 0 0 1-2 1.8h-9a2 2 0 0 1-2-1.8L15 12Z"/><path d="M16 21h16M27 12l4-7"/>',
    ];
    $glassFor = function (array $item) use ($glasses): string {
        $slugs = collect($item['tags'] ?? [])->map(fn ($tag) => Str::slug($tag));

        return match (true) {
            $slugs->contains('wine') || $slugs->contains('sparkling') => $glasses['wine'],
            $slugs->contains('alcohol-free') => $glasses['tall'],
            default => $glasses['cocktail'],
        };
    };
@endphp

<dialog class="dm" id="{{ $id }}" data-dm aria-labelledby="{{ $id }}-title">
    <div class="dm-frame">

        <header class="dm-bar" data-dm-bar>
            <div class="dm-bar-inner">
                <div class="dm-brand">
                    <x-icon name="lotus" :size="44" class="dm-logo" />
                    <div>
                        <h2 class="dm-title" id="{{ $id }}-title">{{ $title }}</h2>
                        <p class="dm-sub">{{ $menu['brand'] }}</p>
                    </div>
                </div>
                <button type="button" class="dm-close" data-dm-close aria-label="Close menu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            @if($categories->count() > 1)
                <nav class="dm-tabs" aria-label="Drink categories" data-dm-tabs>
                    @foreach($categories as $category)
                        <a href="#{{ $id }}-{{ $category['slug'] }}" class="dm-tab{{ $loop->first ? ' is-active' : '' }}" data-dm-tab="{{ $category['slug'] }}">{{ $category['name'] }}</a>
                    @endforeach
                </nav>
            @endif
        </header>

        <div class="dm-scroll" data-dm-scroll tabindex="-1">

            @if($filters->isNotEmpty())
                <div class="dm-filters-wrap" data-dm-filters>
                    <div class="dm-filters" role="group" aria-label="Filter drinks">
                        <span class="dm-filters-label">Filter by</span>
                        <button type="button" class="dm-chip is-on" data-dm-filter="" aria-pressed="true">All drinks</button>
                        @foreach($filters as $label)
                            <button type="button" class="dm-chip" data-dm-filter="{{ Str::slug($label) }}" aria-pressed="false">{{ $label }}</button>
                        @endforeach
                        <span class="dm-found" aria-live="polite" data-dm-found></span>
                    </div>
                </div>
            @endif

            <div class="dm-body">

                @foreach($categories as $category)
                    @php($asCards = ($category['style'] ?? 'list') === 'cards')
                    <section class="dm-section" id="{{ $id }}-{{ $category['slug'] }}" data-dm-section="{{ $category['slug'] }}" aria-labelledby="{{ $id }}-{{ $category['slug'] }}-h">
                        <div class="dm-section-head">
                            <h3 id="{{ $id }}-{{ $category['slug'] }}-h">{{ $category['name'] }}</h3>
                            <span class="dm-rule" aria-hidden="true"></span>
                            <span class="dm-count">{{ count($category['items']) }} {{ count($category['items']) === 1 ? 'drink' : 'drinks' }}</span>
                        </div>

                        <div class="{{ $asCards ? 'dm-cards' : 'dm-list' }}">
                            @foreach($category['items'] as $item)
                                @if($asCards)
                                    <article class="dm-card dm-item" data-tags="{{ $tagSlugs($item) }}">
                                        <div class="dm-photo{{ empty($item['image']) ? ' is-empty' : '' }}">
                                            @if(!empty($item['image']))
                                                <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" draggable="false">
                                            @else
                                                <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $glassFor($item) !!}</svg>
                                            @endif
                                        </div>
                                        <div class="dm-info">
                                            <div class="dm-line">
                                                <h4 class="dm-name">{{ $item['name'] }}</h4>
                                                @if($item['price'] !== '')
                                                    <p class="dm-price"><span>{{ $currency }}</span> {{ $item['price'] }}</p>
                                                @endif
                                            </div>
                                            @if(!empty($item['text']))
                                                <p class="dm-desc">{{ $item['text'] }}</p>
                                            @endif
                                            @if(!empty($item['tags']))
                                                <ul class="dm-tags" aria-label="Details">
                                                    @foreach($item['tags'] as $tag)
                                                        <li>{{ $tag }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </article>
                                @else
                                    <article class="dm-row dm-item" data-tags="{{ $tagSlugs($item) }}">
                                        <div class="dm-line">
                                            <h4 class="dm-name">{{ $item['name'] }}</h4>
                                            @if($item['price'] !== '')
                                                <span class="dm-dots" aria-hidden="true"></span>
                                                <p class="dm-price"><span>{{ $currency }}</span> {{ $item['price'] }}</p>
                                            @endif
                                        </div>
                                        @if(!empty($item['text']))
                                            <p class="dm-desc">{{ $item['text'] }}</p>
                                        @endif
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <p class="dm-empty" data-dm-empty hidden>
                    No drinks match this filter.
                    <button type="button" data-dm-filter="">Show all drinks</button>
                </p>

                <footer class="dm-foot">
                    <p class="dm-note">{{ $total }} {{ $total === 1 ? 'drink' : 'drinks' }}@if($menu['note'] !== '') &middot; {{ $menu['note'] }}@endif</p>
                    <button type="button" class="dm-top" data-dm-top>Back to top</button>
                </footer>
            </div>
        </div>

    </div>
</dialog>
