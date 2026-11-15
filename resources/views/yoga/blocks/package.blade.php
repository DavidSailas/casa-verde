<section class="yg-section" id="package">
    <div class="yg-wrap">
        <span class="yg-label">{{ $page['layout']['labels']['package'] }}</span>
        @if($page['package']['title'])
            <h2>{{ $page['package']['title'] }}</h2>
        @endif

        <div class="yg-package">
            @foreach($page['package']['columns'] as $column)
                <div class="yg-col yg-col--{{ $column['style'] }}">
                    <h3>{{ $column['title'] }}</h3>
                    <ul role="list">
                        @foreach($column['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @if($page['package']['price'])
                <aside class="yg-price">
                    <h3>{{ $page['package']['price']['title'] }}</h3>
                    @if($page['package']['price']['big'])
                        <p class="yg-price__big">{{ $page['package']['price']['big'] }}</p>
                    @endif
                    @if($page['package']['price']['lead'])
                        <p>{{ $page['package']['price']['lead'] }}</p>
                    @endif
                    @if(count($page['package']['price']['bullets']))
                        <ul role="list">
                            @foreach($page['package']['price']['bullets'] as $bullet)
                                <li>{{ $bullet }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($page['package']['price']['button_text'])
                        <a href="{{ $href($page['package']['price']['button_url'], 'retreat') }}" class="yg-btn yg-btn--gold yg-btn--block">{{ $page['package']['price']['button_text'] }}</a>
                    @endif
                </aside>
            @endif
        </div>

        @if($page['package']['next'])
            <div class="yg-strip">
                <div>
                    <h3>{{ $page['package']['next']['title'] }}</h3>
                    <p>{{ $page['package']['next']['text'] }}</p>
                </div>
                @if($page['package']['next']['button_text'])
                    <a href="{{ $href($page['package']['next']['button_url']) }}" class="yg-btn yg-btn--outline-dark yg-btn--sm">{{ $page['package']['next']['button_text'] }} <span aria-hidden="true">→</span></a>
                @endif
            </div>
        @endif

        @if($page['package']['share'])
            <div class="yg-strip yg-strip--white">
                <div>
                    <h3>{{ $page['package']['share']['title'] }}</h3>
                    <p>{{ $page['package']['share']['text'] }}</p>
                </div>
                @if($page['package']['share']['button_text'])
                    <a href="{{ $href($page['package']['share']['button_url']) }}" class="yg-btn yg-btn--green yg-btn--sm">{{ $page['package']['share']['button_text'] }}</a>
                @endif
            </div>
        @endif
    </div>
</section>
