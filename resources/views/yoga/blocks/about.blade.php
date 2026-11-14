<section class="yg-section" id="about">
    <div class="yg-wrap yg-split">
        <div class="yg-split__head">
            <span class="yg-label">{{ $page['intro']['eyebrow'] }}</span>
            <h2>{{ $page['intro']['title'] }}</h2>
        </div>
        <div class="yg-split__text">
            @if($page['intro']['lead'])
                <p>{{ $page['intro']['lead'] }}</p>
            @endif
            @if(count($page['intro']['more']))
                <details class="yg-more">
                    <summary>Read more</summary>
                    @foreach($page['intro']['more'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </details>
            @endif
        </div>
    </div>

    @if(count($page['cards']))
        <div class="yg-wrap">
            <div class="yg-cards">
                @foreach($page['cards'] as $card)
                    <article class="yg-card">
                        <h3>{{ $card['title'] }}</h3>
                        <p>{{ $card['text'] }}</p>
                        @if($card['meta'])
                            <small>{{ $card['meta'] }}</small>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</section>
