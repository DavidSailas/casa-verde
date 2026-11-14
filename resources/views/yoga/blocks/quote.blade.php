<section class="yg-section" id="quote">
    <div class="yg-wrap">
        <blockquote class="yg-quote">
            <p>{{ $page['quote']['text'] }}</p>
            <footer>
                <strong>{{ $page['quote']['title'] }}</strong>
                @if($page['quote']['eyebrow'])
                    <span>{{ $page['quote']['eyebrow'] }}</span>
                @endif
            </footer>
        </blockquote>
    </div>
</section>
