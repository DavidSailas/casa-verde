<section class="yg-section" id="teacher">
    <div class="yg-wrap yg-teacher">
        @if($page['teacher']['image'])
            <figure class="yg-teacher__photo">
                <img src="{{ $img($page['teacher']['image']) }}" alt="{{ $page['teacher']['title'] }}" loading="lazy">
                @if($page['teacher']['caption'])
                    <figcaption>{{ $page['teacher']['caption'] }}</figcaption>
                @endif
            </figure>
        @endif

        <div class="yg-teacher__text">
            <span class="yg-label">{{ $page['layout']['labels']['teacher'] }}</span>
            <h2>{{ $page['teacher']['title'] }}</h2>
            @if($page['teacher']['role'])
                <p class="yg-role">{{ $page['teacher']['role'] }}</p>
            @endif
            @if($page['teacher']['lead'])
                <p>{{ $page['teacher']['lead'] }}</p>
            @endif
            @if(count($page['teacher']['more']))
                <details class="yg-more">
                    <summary>Read more</summary>
                    @foreach($page['teacher']['more'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </details>
            @endif

            @if(count($page['teacher']['styles']))
                <h3>Styles &amp; focus</h3>
                <ul class="yg-tags" role="list">
                    @foreach($page['teacher']['styles'] as $style)
                        <li>{{ $style }}</li>
                    @endforeach
                </ul>
            @endif

            @if($page['extra'])
                <article class="yg-extra">
                    @if($page['extra']['badge'])
                        <span class="yg-badge">{{ $page['extra']['badge'] }}</span>
                    @endif
                    <h3>{{ $page['extra']['title'] }}</h3>
                    <p>{{ $page['extra']['lead'] }}</p>
                    @if($page['extra']['gold'])
                        <p class="yg-extra__gold">{{ $page['extra']['gold'] }}</p>
                    @endif
                    @if($page['extra']['button_text'])
                        <a href="{{ $href($page['extra']['button_url']) }}" class="yg-btn yg-btn--white yg-btn--sm">{{ $page['extra']['button_text'] }} <span aria-hidden="true">→</span></a>
                    @endif
                </article>
            @endif
        </div>
    </div>
</section>
