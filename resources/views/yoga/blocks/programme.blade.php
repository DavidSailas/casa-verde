<section class="yg-dark" id="programme">
    <div class="yg-wrap">
        <span class="yg-label yg-label--gold">{{ $page['programme']['header']['eyebrow'] }}</span>
        <h2>{{ $page['programme']['header']['title'] }}</h2>

        @if(count($page['programme']['stats']))
            <ul class="yg-numbers" role="list">
                @foreach($page['programme']['stats'] as $stat)
                    <li>
                        <strong>{{ $stat['title'] }}</strong>
                        <span>{{ $stat['eyebrow'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="yg-flow" id="schedule" data-yg-tabs>
            <div class="yg-flow__side">
                @if($page['programme']['flow'])
                    <h3>{{ $page['programme']['flow']['title'] }}</h3>
                    @if($page['programme']['flow']['note'])
                        <span class="yg-note-pill">{{ $page['programme']['flow']['note'] }}</span>
                    @endif
                    @if($page['programme']['flow']['text'])
                        <p>{{ $page['programme']['flow']['text'] }}</p>
                    @endif
                @endif

                @if(count($page['programme']['days']))
                    <div class="yg-days" role="tablist" aria-label="Choose a day" data-yg-tablist hidden>
                        @foreach($page['programme']['days'] as $day)
                            <button type="button" role="tab" id="yg-tab-{{ $day['n'] }}" aria-controls="yg-panel-{{ $day['n'] }}" data-yg-tab @if($loop->first) class="is-active" aria-selected="true" @else aria-selected="false" tabindex="-1" @endif>
                                <small>{{ $day['label'] }}</small>
                                <span>{{ $day['name'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="yg-flow__main">
                @foreach($page['programme']['panels'] as $panel)
                    <div class="yg-day" id="yg-panel-{{ $panel['n'] }}" data-yg-panel @if($panel['label']) role="tabpanel" aria-labelledby="yg-tab-{{ $panel['n'] }}" @endif>
                        @if($panel['label'])
                            <span class="yg-label yg-label--gold">{{ $panel['label'] }} · {{ $panel['name'] }}</span>
                        @endif
                        @if($panel['heading'])
                            <h3>{{ $panel['heading'] }}</h3>
                        @endif
                        <ol class="yg-times">
                            @foreach($panel['items'] as $slot)
                                <li>
                                    <time>{{ $slot['time'] }}</time>
                                    <div>
                                        <strong>{{ $slot['name'] }}</strong>
                                        @if($slot['text'])
                                            <p>{{ $slot['text'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        </div>

        @if($page['programme']['care'])
            <div class="yg-care">
                <h3>{{ $page['programme']['care']['title'] }}</h3>
                <ul>
                    @foreach($page['programme']['care']['items'] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            @if($page['programme']['care']['note'])
                <p class="yg-small yg-small--light">{{ $page['programme']['care']['note'] }}</p>
            @endif
        @endif
    </div>
</section>
