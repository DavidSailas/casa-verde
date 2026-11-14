<section class="yg-banner" style="background-image:url('{{ $img($page['cta']['image']) }}')">
    <h2>{{ $page['cta']['title'] }}</h2>
    <div class="yg-actions">
        @foreach($page['cta']['buttons'] as $button)
            @if($loop->first)
                <a href="{{ $href($button['url']) }}" class="yg-btn yg-btn--gold">{{ $button['text'] }}</a>
            @else
                <a href="{{ $href($button['url']) }}" class="yg-btn yg-btn--outline">{{ $button['text'] }}</a>
            @endif
        @endforeach
    </div>
</section>
