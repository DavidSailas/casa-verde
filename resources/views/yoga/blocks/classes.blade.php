<section class="yg-section yg-section--sand" id="classes">
    <div class="yg-wrap yg-classes">
        <div class="yg-classes__info">
            <span class="yg-label">{{ $page['layout']['labels']['classes'] }}</span>
            <h2>{{ $page['classes']['info']['title'] }}</h2>
            @if($page['classes']['info']['lead'])
                <p>{{ $page['classes']['info']['lead'] }}</p>
            @endif
            @foreach($page['classes']['info']['more'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
            @if($page['classes']['info']['note'])
                <p class="yg-callout">{{ $page['classes']['info']['note'] }}</p>
            @endif
        </div>

        <div class="yg-classes__list">
            <div class="yg-sessions">
                @foreach($page['classes']['groups'] as $group)
                    <article class="yg-day-card">
                        <h3>{{ $group['label'] }}</h3>
                        @foreach($group['sessions'] as $session)
                            <div class="yg-session">
                                <div>
                                    <strong>{{ $session['name'] }}</strong>
                                    @if($session['meta'])
                                        <small>{{ $session['meta'] }}</small>
                                    @endif
                                </div>
                                @if($session['button_text'])
                                    <a href="{{ $href($session['button_url']) }}" class="yg-btn yg-btn--green yg-btn--sm">{{ $session['button_text'] }} <span aria-hidden="true">→</span></a>
                                @endif
                            </div>
                        @endforeach
                    </article>
                @endforeach
            </div>
            @if($page['classes']['info']['small'])
                <p class="yg-small">{{ $page['classes']['info']['small'] }}</p>
            @endif
        </div>
    </div>
</section>
