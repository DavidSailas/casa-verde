<section class="yg-section" id="gallery">
    <div class="yg-wrap">
        <span class="yg-label">{{ $page['layout']['labels']['gallery'] }}</span>
        <div class="yg-gallery__head">
            <div>
                @if($page['gallery']['head'])
                    <h2>{{ $page['gallery']['head']['title'] }}</h2>
                    @if($page['gallery']['head']['text'])
                        <p>{{ $page['gallery']['head']['text'] }}</p>
                    @endif
                @endif
            </div>
            <div class="yg-arrows">
                <button type="button" class="yg-arrow" data-yg-prev aria-label="Previous photo">‹</button>
                <button type="button" class="yg-arrow" data-yg-next aria-label="Next photo">›</button>
            </div>
        </div>
    </div>

    <div class="yg-slider" data-yg-slider tabindex="0" aria-label="Photo gallery">
        @foreach($page['gallery']['items'] as $photo)
            <figure class="yg-slide">
                <img src="{{ $img($photo['image']) }}" alt="{{ $photo['caption'] }}" loading="lazy">
                @if($photo['caption'])
                    <figcaption>{{ $photo['caption'] }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>

    <div class="yg-wrap">
        <p class="yg-count" data-yg-count>1 / {{ count($page['gallery']['items']) }}</p>
    </div>
</section>
