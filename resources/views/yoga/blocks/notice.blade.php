<section class="yg-section yg-section--tight" id="notice">
    <div class="yg-wrap">
        <div class="yg-notice">
            @if($page['notice']['label'])
                <div class="yg-notice__label"><span class="yg-dot" aria-hidden="true"></span>{{ $page['notice']['label'] }}</div>
            @endif
            <div class="yg-notice__body">
                @if($page['notice']['title'])
                    <h2>{{ $page['notice']['title'] }}</h2>
                @endif
                @if($page['notice']['lead'])
                    <p>{{ $page['notice']['lead'] }}</p>
                @endif
                @foreach($page['notice']['more'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </div>
</section>
