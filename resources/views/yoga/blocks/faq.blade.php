<section class="yg-section yg-section--sand" id="faq">
    <div class="yg-wrap yg-faq">
        <div class="yg-faq__head">
            <span class="yg-label">{{ $page['layout']['labels']['faq'] }}</span>
            <h2>{{ $page['faq']['title'] }}</h2>
        </div>
        <div class="yg-faq__list">
            @foreach($page['faq']['items'] as $entry)
                <details class="yg-faq__item" @if($loop->first) open @endif>
                    <summary>{{ $entry['title'] }}</summary>
                    <p>{{ $entry['text'] }}</p>
                </details>
            @endforeach
        </div>
    </div>

    {{-- schema:start --}}
    @php
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $entry): array => [
                '@type' => 'Question',
                'name' => $entry['title'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $entry['text']],
            ], $page['faq']['items']),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    {{-- schema:end --}}
</section>
