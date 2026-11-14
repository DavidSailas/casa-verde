<section class="yg-section" id="resort">
    <div class="yg-wrap yg-resort">
        <div class="yg-resort__text">
            <span class="yg-label">{{ $page['layout']['labels']['resort'] }}</span>
            @if($page['resort']['text'])
                <h2>{{ $page['resort']['text']['title'] }}</h2>
                @if($page['resort']['text']['lead'])
                    <p>{{ $page['resort']['text']['lead'] }}</p>
                @endif
                @if(count($page['resort']['text']['more']))
                    <details class="yg-more">
                        <summary>Read more</summary>
                        @foreach($page['resort']['text']['more'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </details>
                @endif
            @endif

            @if(count($page['resort']['list']))
                @if($page['resort']['list_title'])
                    <h3>{{ $page['resort']['list_title'] }}</h3>
                @endif
                <ul class="yg-checks" role="list">
                    @foreach($page['resort']['list'] as $line)
                        <li>
                            <strong>{{ $line['title'] }}</strong>
                            @if($line['text'])
                                <span>{{ $line['text'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if(count($page['resort']['photos']))
            <div class="yg-collage">
                @foreach($page['resort']['photos'] as $photo)
                    <figure @if($loop->first) class="yg-collage__lead" @endif>
                        <img src="{{ $img($photo['image']) }}" alt="{{ $photo['caption'] }}" loading="lazy">
                        @if($photo['caption'])
                            <figcaption>{{ $photo['caption'] }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </div>

    @if($page['resort']['spotlight'])
        <div class="yg-wrap">
            <article class="yg-spot">
                <div class="yg-spot__photo">
                    <img src="{{ $img($page['resort']['spotlight']['image']) }}" alt="{{ $page['resort']['spotlight']['title'] }}" loading="lazy">
                </div>
                <div class="yg-spot__text">
                    @if($page['resort']['spotlight']['badge'])
                        <span class="yg-badge">{{ $page['resort']['spotlight']['badge'] }}</span>
                    @endif
                    <h3>{{ $page['resort']['spotlight']['title'] }}</h3>
                    <p>{{ $page['resort']['spotlight']['lead'] }}</p>
                </div>
            </article>
        </div>
    @endif
</section>
