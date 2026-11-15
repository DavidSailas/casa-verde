@extends('admin.layout')

@section('title', $item->exists ? 'Edit entry' : 'New entry')

@section('content')

    @php
        $meta = \App\Models\HomeContent::sectionMeta(old('section', $item->section));
        $label = fn (string $field, string $default) => $meta['fields'][$field] ?? $default;
        $show = fn (string $field) => ! in_array($field, $meta['hide'] ?? [], true);
        $hint = fn (string $field) => $meta['hints'][$field] ?? null;
        $isArticle = old('section', $item->section) === 'journal_articles';
    @endphp

    <a href="{{ route('admin.content.index', $item->section ? ['section' => $item->section] : []) }}"
       class="inline-flex items-center gap-1 text-sm text-forest-700 hover:text-forest-900 hover:underline">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to {{ $item->section ? $item->section_name : 'site content' }}
    </a>

    <h1 class="page-title mt-4">{{ $item->exists ? $item->display_title : 'New entry' }}</h1>
    <p class="mt-3 text-ink-soft">
        @if ($item->exists)
            Entry in {{ $item->section_name }}. Changes appear on the live site as soon as you save.
        @else
            Add a photo or block of text to the site.
        @endif
    </p>

    @if (! empty($meta['help']))
        <div class="mt-5 flex gap-3 rounded-xl border border-gold-400/40 bg-gold-400/10 px-4 py-3 text-sm text-forest-900" role="note">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.04-.02a.75.75 0 011.06.85l-.7 2.8a.75.75 0 001.06.85M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.01"/></svg>
            <p><span class="font-medium">{{ $item->section_name }}.</span> {{ $meta['help'] }}</p>
        </div>
    @endif

    <form method="POST"
          action="{{ $item->exists ? route('admin.content.update', $item) : route('admin.content.store') }}"
          enctype="multipart/form-data"
          class="mt-8 grid gap-6 pb-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <div class="space-y-6">

            @if ($show('title') || $show('subtitle') || $show('description') || $show('features'))
            <section class="panel" aria-labelledby="text-heading">
                <h2 id="text-heading" class="section-title">Text</h2>

                <div class="mt-5 space-y-5">
                    @if ($show('title'))
                    <div>
                        <label for="title" class="field-label">{{ $label('title', 'Title') }}</label>
                        <textarea id="title" name="title" rows="2" class="field-input">{{ old('title', $item->title) }}</textarea>
                        <p class="field-hint">{{ $hint('title') ?? 'Press Enter to start a new line. Some headings are shown on separate lines on the site.' }}</p>
                    </div>
                    @endif

                    @if ($show('subtitle'))
                    <div>
                        <label for="subtitle" class="field-label">{{ $label('subtitle', 'Subtitle') }}</label>
                        @if (! empty($meta['select_source']))
                            @php
                                // The link to another list (a Winterresidenz tab, a menu category) is picked by name.
                                $choice = \App\Support\AdminChoices::for($meta['select_source']);
                                $currentChoice = trim((string) old('subtitle', $item->subtitle));
                                $knownChoice = $currentChoice === ''
                                    || array_key_exists($currentChoice, $choice['options'])
                                    || collect($choice['options'])->contains(fn (string $name): bool => strcasecmp($name, $currentChoice) === 0);
                            @endphp
                            <select id="subtitle" name="subtitle" class="field-input">
                                <option value="">{{ $choice['label'] }}</option>
                                @foreach ($choice['options'] as $value => $name)
                                    <option value="{{ $value }}" @selected($currentChoice === (string) $value || strcasecmp($currentChoice, $name) === 0)>{{ is_int($value) && $meta['select_source'] === 'winter_tabs' ? $value.' – ' : '' }}{{ $name }}</option>
                                @endforeach
                                @unless ($knownChoice)
                                    <option value="{{ $currentChoice }}" selected>{{ $currentChoice }} (not in the list)</option>
                                @endunless
                            </select>
                        @else
                            <textarea id="subtitle" name="subtitle" rows="2" class="field-input">{{ old('subtitle', $item->subtitle) }}</textarea>
                        @endif
                        @if ($hint('subtitle'))
                            <p class="field-hint">{{ $hint('subtitle') }}</p>
                        @endif
                    </div>
                    @endif

                    @if ($show('description'))
                    <div>
                        <label for="description" class="field-label">{{ $label('description', 'Description') }}</label>
                        <textarea id="description" name="description" rows="{{ $isArticle ? 18 : ($meta['rows']['description'] ?? 5) }}" @if ($isArticle) data-article-editor @endif class="field-input">{{ old('description', $item->description) }}</textarea>
                        @if ($hint('description'))
                            <p class="field-hint">{{ $hint('description') }}</p>
                        @endif
                    </div>
                    @endif

                    @if ($show('features'))
                    <div>
                        <label for="features" class="field-label">{{ $label('features', 'Features') }}</label>
                        <textarea id="features" name="features" rows="4" placeholder="One feature per line" class="field-input">{{ old('features', is_array($item->features) ? implode("\n", $item->features) : '') }}</textarea>
                        <p class="field-hint">{{ $hint('features') ?? 'Shown as a bullet list, one item per line, for example the amenities of a villa.' }}</p>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            @if ($show('image'))
            <section class="panel" aria-labelledby="photo-heading">
                <h2 id="photo-heading" class="section-title">{{ $label('image', 'Photo') }}</h2>

                <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-start">
                    <div class="aspect-[4/3] w-full shrink-0 overflow-hidden rounded-lg border border-sand-300 bg-sand-100 sm:w-60">
                        <img data-image-preview src="{{ $item->image ? asset($item->image) : '' }}" alt="Current photo"
                             class="h-full w-full object-cover {{ $item->image ? '' : 'hidden' }}">
                        <div data-image-empty class="h-full items-center justify-center text-sm text-ink-soft {{ $item->image ? 'hidden' : 'flex' }}">
                            No photo yet
                        </div>
                    </div>

                    <div>
                        <input type="file" id="image" name="image" accept="image/*,.svg" data-image-input class="peer sr-only">
                        <label for="image" class="btn-secondary cursor-pointer peer-focus-visible:ring-4 peer-focus-visible:ring-gold-400/40">
                            {{ $item->image ? 'Replace photo' : 'Choose photo' }}
                        </label>
                        <p data-image-name class="mt-3 text-sm text-forest-800" aria-live="polite"></p>
                        <p class="field-hint">JPG, PNG, WebP or SVG, up to 5 MB. A new photo replaces the current one.</p>
                    </div>
                </div>
            </section>
            @endif

            @if (! empty($meta['gallery']))
            @php
                $galleryPhotos = array_values(array_filter((array) old('gallery', $item->gallery ?? []), 'is_string'));
            @endphp
            <section class="panel" aria-labelledby="gallery-heading" data-gallery-box>
                <h2 id="gallery-heading" class="section-title">Gallery photos</h2>
                <p class="field-hint">These photos slide open, one after another, when a visitor clicks the picture of this villa. The first photo opens in the middle. Add as many as you like (8 looks best).</p>

                <input type="hidden" name="gallery_present" value="1">

                <ul data-gallery-list class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Gallery photos, in the order visitors see them">
                    @foreach ($galleryPhotos as $path)
                        <li data-gallery-item class="overflow-hidden rounded-lg border border-sand-300 bg-white">
                            <input type="hidden" name="gallery[]" value="{{ $path }}">
                            <div class="relative aspect-[4/3] bg-sand-100">
                                <img src="{{ asset($path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                <span data-gallery-number class="absolute left-2 top-2 rounded-full bg-forest-900/80 px-2.5 py-0.5 text-xs font-medium text-white">{{ $loop->iteration }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 px-2.5 py-2">
                                <div class="flex gap-1.5">
                                    <button type="button" data-gallery-move="-1" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-sand-300 bg-white text-forest-800 hover:bg-sand-100 focus-visible:ring-4 focus-visible:ring-gold-400/40" aria-label="Move this photo earlier">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                    </button>
                                    <button type="button" data-gallery-move="1" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-sand-300 bg-white text-forest-800 hover:bg-sand-100 focus-visible:ring-4 focus-visible:ring-gold-400/40" aria-label="Move this photo later">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                    </button>
                                </div>
                                <button type="button" data-gallery-remove class="rounded-full px-3 py-1 text-sm text-red-700 hover:bg-red-50 focus-visible:ring-4 focus-visible:ring-red-200">Remove</button>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <p data-gallery-empty class="mt-5 rounded-lg border border-dashed border-sand-300 px-4 py-6 text-center text-sm text-ink-soft {{ count($galleryPhotos) ? 'hidden' : '' }}">
                    No gallery photos yet. Add some below. Until you do, the villa uses its card photo and the photos that came with the site.
                </p>

                <div class="mt-6 border-t border-sand-300 pt-5">
                    <input type="file" id="gallery_new" name="gallery_new[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" data-gallery-input class="peer sr-only">
                    <label for="gallery_new" class="btn-secondary cursor-pointer peer-focus-visible:ring-4 peer-focus-visible:ring-gold-400/40">Add photos</label>
                    <p data-gallery-new-note class="mt-3 text-sm text-forest-800" aria-live="polite"></p>
                    <div data-gallery-new-preview class="mt-3 grid gap-3 sm:grid-cols-3 xl:grid-cols-4"></div>
                    <p class="field-hint">Choose one or several photos (JPG, PNG, WebP or GIF, up to 5 MB each, up to 20 at a time). Landscape photos at least 900 px wide look best. New photos are added at the end; save, then use the arrows to put them in order. If saving fails, add fewer photos at a time.</p>
                    @error('gallery_new.*')<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    @error('gallery_new')<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                </div>

                <p class="field-hint mt-4">Remove, add and reorder as you like, then press Save changes. Nothing changes on the live site until you save.</p>
            </section>
            @endif

            @if (! empty($meta['villa_details']))
            @php
                // What the detail page shows now: this villa's saved content, else the starting content that came with the site.
                $vd = $item->exists ? \App\Support\VillaDetails::for($item) : [];
                $vdSlug = \Illuminate\Support\Str::slug((string) $item->title);

                $vdHighlights = old('details.highlights');
                if ($vdHighlights === null) {
                    $vdHighlights = $vd['highlights'] ?? [];

                    // Highlights edited earlier in "Villa highlights (icon row)" keep showing until this box is saved.
                    if (! in_array('highlights', $vd['custom'] ?? [], true) && $vdSlug !== '') {
                        $older = \App\Models\HomeContent::where('section', 'villas_highlights')->orderBy('sort_order')->get()
                            ->filter(fn ($row) => \Illuminate\Support\Str::slug((string) $row->subtitle) === $vdSlug)
                            ->map(fn ($row) => array_filter(['icon' => $row->icon, 'icon2' => $row->image, 'label' => $row->title]))
                            ->values()->all();
                        if ($older !== []) {
                            $vdHighlights = $older;
                        }
                    }
                }
                $vdHighlights = array_values((array) $vdHighlights);
                $vdFilled = count(array_filter($vdHighlights, fn ($row) => filled($row['label'] ?? null)));
                $vdRows = 8;

                $vdIcons = ['villa', 'family', 'couple', 'bed', 'queen-bed', 'beds', 'bath', 'jacuzzi', 'terrace', 'wifi', 'lotus', 'leaf',
                    'sunset', 'island', 'tropical-home', 'privacy-lock', 'shield', 'coffee', 'breakfast', 'cutlery', 'wine', 'cocktail',
                    'laptop', 'housekeeping', 'spaset', 'suitcase', 'calendar', 'snorkel'];

                $vdAmenities = old('details.amenities');
                if ($vdAmenities === null) {
                    $vdAmenities = collect($vd['amenities'] ?? [])->map(fn ($column) => implode("\n", (array) $column))->all();
                }
                $vdStoryTitle = old('details.story_title', implode("\n", (array) ($vd['story_title'] ?? [])));
                $vdStoryText = old('details.story_text', implode("\n\n", (array) ($vd['story_text'] ?? [])));
                $vdIntro = old('details.intro', in_array('intro', $vd['custom'] ?? [], true) ? ($vd['intro'] ?? '') : '');
                $vdBedrooms = old('details.bedrooms');
                if ($vdBedrooms === null) {
                    $vdBedrooms = $vd['bedrooms'] ?? [];
                }
                $vdBedrooms = array_values((array) $vdBedrooms);
                $vdBedroomsFilled = count(array_filter($vdBedrooms, fn ($row) => filled($row['name'] ?? null) || filled($row['beds'] ?? null)));
                $vdBedroomRows = 8;
                $vdGuests = old('details.capacity.guests', $vd['capacity']['guests'] ?? '');
                $vdBathrooms = old('details.capacity.bathrooms', $vd['capacity']['bathrooms'] ?? '');
                $vdInterior = old('details.layout.interior', $vd['layout']['interior'] ?? '');
                $vdTerrace = old('details.layout.terrace', $vd['layout']['terrace'] ?? '');
                $vdHero = in_array('hero', $vd['custom'] ?? [], true) ? ($vd['hero'] ?? null) : null;
                $vdPlan = $vd['layout']['plan'] ?? null;
                $vdIconField = 'rounded-lg border border-sand-300 bg-white px-3 py-2 text-sm text-forest-900 focus:border-forest-700 focus:outline-none focus:ring-4 focus:ring-gold-400/40';
            @endphp
            <section class="panel" aria-labelledby="details-heading">
                <h2 id="details-heading" class="section-title">Villa detail page</h2>
                <p class="field-hint">Everything on this villa's own page (the one visitors reach from the “View” button). Leave a box empty to skip that part. The villa name, tagline, price and photos above are used too.</p>

                <input type="hidden" name="details_present" value="1">

                <div class="mt-6 space-y-8">

                    <div>
                        <label for="details_intro" class="field-label">Introduction</label>
                        <textarea id="details_intro" name="details[intro]" rows="3" class="field-input">{{ $vdIntro }}</textarea>
                        <p class="field-hint">Optional. Leave empty to use the Introduction written above.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="details_story_title" class="field-label">Story heading</label>
                            <textarea id="details_story_title" name="details[story_title]" rows="2" class="field-input" placeholder="Designed for families.&#10;Made for togetherness.">{{ $vdStoryTitle }}</textarea>
                            <p class="field-hint">One line per row; two short lines look best.</p>
                        </div>
                        <div>
                            <label for="details_story_text" class="field-label">Story text</label>
                            <textarea id="details_story_text" name="details[story_text]" rows="5" class="field-input">{{ $vdStoryText }}</textarea>
                            <p class="field-hint">Leave an empty line between paragraphs. The photos beside it are the first three Gallery photos.</p>
                        </div>
                    </div>

                    <fieldset data-reveal-box>
                        <legend class="field-label">Highlights (icon row under the photo)</legend>
                        <p class="field-hint">Up to 8. Pick an icon, type the label, and (only if you want two icons side by side) a second icon.</p>

                        <div class="mt-3 space-y-3">
                            @for ($i = 0; $i < $vdRows; $i++)
                                @php
                                    $row = $vdHighlights[$i] ?? [];
                                    $hidden = $i >= max(4, $vdFilled + 1);
                                @endphp
                                <div data-reveal-row class="grid gap-2 sm:grid-cols-[11rem_minmax(0,1fr)_9rem] {{ $hidden ? 'hidden' : '' }}">
                                    @foreach (['icon' => 'Icon', 'icon2' => '2nd icon'] as $key => $caption)
                                        @php $current = trim((string) ($row[$key] ?? '')); @endphp
                                        <select name="details[highlights][{{ $i }}][{{ $key }}]" aria-label="{{ $caption }} of highlight {{ $i + 1 }}"
                                                class="{{ $vdIconField }} {{ $key === 'icon2' ? 'sm:order-3' : '' }}">
                                            <option value="">{{ $key === 'icon' ? 'Choose an icon' : 'No 2nd icon' }}</option>
                                            @foreach ($vdIcons as $name)
                                                <option value="{{ $name }}" @selected($current === $name)>{{ $name }}</option>
                                            @endforeach
                                            @if ($current !== '' && ! in_array($current, $vdIcons, true))
                                                <option value="{{ $current }}" selected>Current: {{ \Illuminate\Support\Str::limit($current, 24) }}</option>
                                            @endif
                                        </select>
                                        @if ($key === 'icon')
                                            <input type="text" name="details[highlights][{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" maxlength="120"
                                                   placeholder="Label, for example Two Bedrooms" aria-label="Label of highlight {{ $i + 1 }}"
                                                   class="{{ $vdIconField }} sm:order-2">
                                        @endif
                                    @endforeach
                                </div>
                            @endfor
                        </div>

                        @if ($vdFilled < $vdRows)
                            <button type="button" data-reveal-more class="btn-secondary mt-3">Add another highlight</button>
                        @endif
                    </fieldset>

                    <fieldset data-reveal-box>
                        <legend class="field-label">Bedrooms and sleeping arrangement</legend>
                        <p class="field-hint">One row per bedroom, up to 8: the room name and its beds (for example “Bedroom 1” and “1 double bed”). The number of bedrooms shown on the villa card and page is counted from these rows, so add or remove a row and everything updates. Leave all rows empty to hide this part.</p>

                        <div class="mt-3 space-y-3">
                            @for ($i = 0; $i < $vdBedroomRows; $i++)
                                @php
                                    $room = $vdBedrooms[$i] ?? [];
                                    $roomHidden = $i >= max(3, $vdBedroomsFilled + 1);
                                @endphp
                                <div data-reveal-row class="grid gap-2 sm:grid-cols-[12rem_minmax(0,1fr)] {{ $roomHidden ? 'hidden' : '' }}">
                                    <input type="text" name="details[bedrooms][{{ $i }}][name]" value="{{ $room['name'] ?? '' }}" maxlength="80"
                                           placeholder="Bedroom {{ $i + 1 }}" aria-label="Name of bedroom {{ $i + 1 }}"
                                           class="{{ $vdIconField }}">
                                    <input type="text" name="details[bedrooms][{{ $i }}][beds]" value="{{ $room['beds'] ?? '' }}" maxlength="120"
                                           placeholder="Beds, for example 1 double bed" aria-label="Beds in bedroom {{ $i + 1 }}"
                                           class="{{ $vdIconField }}">
                                </div>
                            @endfor
                        </div>

                        @if ($vdBedroomsFilled < $vdBedroomRows)
                            <button type="button" data-reveal-more class="btn-secondary mt-3">Add another bedroom</button>
                        @endif

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="details_guests" class="field-label">Sleeps (guests)</label>
                                <input type="number" min="0" max="99" step="1" id="details_guests" name="details[capacity][guests]" value="{{ $vdGuests }}" class="field-input" placeholder="6">
                            </div>
                            <div>
                                <label for="details_bathrooms" class="field-label">Bathrooms</label>
                                <input type="number" min="0" max="99" step="1" id="details_bathrooms" name="details[capacity][bathrooms]" value="{{ $vdBathrooms }}" class="field-input" placeholder="2">
                            </div>
                        </div>
                        <p class="field-hint">Shown as small facts on the villa card and the villa page. Leave empty to hide.</p>
                    </fieldset>

                    <div>
                        <p class="field-label">Amenities</p>
                        <p class="field-hint">Three columns on the page. One amenity per line.</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            @foreach ([0 => 'Column 1', 1 => 'Column 2', 2 => 'Column 3'] as $i => $caption)
                                <div>
                                    <label for="details_amenities_{{ $i }}" class="field-label">{{ $caption }}</label>
                                    <textarea id="details_amenities_{{ $i }}" name="details[amenities][{{ $i }}]" rows="6" class="field-input" placeholder="Air Conditioning&#10;Coffee &amp; Tea Station">{{ $vdAmenities[$i] ?? '' }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="field-label">Floor area (square metres)</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="details_interior" class="field-label">Interior</label>
                                <input type="number" min="0" step="1" id="details_interior" name="details[layout][interior]" value="{{ $vdInterior }}" class="field-input">
                            </div>
                            <div>
                                <label for="details_terrace" class="field-label">Terrace</label>
                                <input type="number" min="0" step="1" id="details_terrace" name="details[layout][terrace]" value="{{ $vdTerrace }}" class="field-input">
                            </div>
                        </div>
                        <p class="field-hint">The total is worked out for you. Leave both empty to hide the floor area.</p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ([
                            ['key' => 'hero', 'file' => 'detail_hero', 'remove' => 'remove_detail_hero', 'path' => $vdHero, 'title' => 'Wide photo', 'hint' => 'The large picture at the top of the villa page. Empty uses the card photo.'],
                            ['key' => 'plan', 'file' => 'detail_plan', 'remove' => 'remove_detail_plan', 'path' => $vdPlan, 'title' => 'Floor plan picture', 'hint' => 'Optional drawing shown beside the floor area.'],
                        ] as $pic)
                            <div>
                                <p class="field-label">{{ $pic['title'] }}</p>
                                @if ($pic['path'])
                                    <img src="{{ asset($pic['path']) }}" alt="Current {{ strtolower($pic['title']) }}" class="mt-2 h-28 w-full rounded-lg border border-sand-300 bg-sand-100 object-cover">
                                    <input type="hidden" name="details[{{ $pic['key'] }}]" value="{{ $pic['path'] }}">
                                    <label class="mt-2 flex items-center gap-2 text-sm text-ink-soft">
                                        <input type="checkbox" name="{{ $pic['remove'] }}" value="1"> Remove this picture
                                    </label>
                                @endif
                                <input type="file" id="{{ $pic['file'] }}" name="{{ $pic['file'] }}" accept="image/jpeg,image/png,image/webp" class="field-input mt-3">
                                <p class="field-hint">{{ $pic['hint'] }} JPG, PNG or WebP, up to 5 MB.</p>
                                @error($pic['file'])<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>

                </div>
            </section>
            @endif

            @if ($show('button_text') || $show('button_url'))
            <section class="panel" aria-labelledby="button-heading">
                <h2 id="button-heading" class="section-title">{{ $show('button_text') ? 'Button' : 'Link' }}</h2>
                @if ($show('button_text'))
                    <p class="field-hint">Leave both empty if this entry has no button.</p>
                @endif

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    @if ($show('button_text'))
                    <div>
                        <label for="button_text" class="field-label">{{ $label('button_text', 'Button text') }}</label>
                        <input type="text" id="button_text" name="button_text" value="{{ old('button_text', $item->button_text) }}" class="field-input">
                    </div>
                    @endif
                    @if ($show('button_url'))
                    <div>
                        <label for="button_url" class="field-label">{{ $label('button_url', 'Button link') }}</label>
                        <input type="text" id="button_url" name="button_url" value="{{ old('button_url', $item->button_url) }}" class="field-input">
                        @if ($hint('button_url'))
                            <p class="field-hint">{{ $hint('button_url') }}</p>
                        @endif
                    </div>
                    @endif
                </div>
            </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="panel" aria-labelledby="settings-heading">
                <h2 id="settings-heading" class="section-title">Settings</h2>

                <div class="mt-5 space-y-5">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" id="active" name="active" value="1" class="peer sr-only"
                               {{ old('active', $item->active ?? true) ? 'checked' : '' }}>
                        <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-sand-300 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:bg-forest-700 peer-checked:after:translate-x-5 peer-focus-visible:ring-4 peer-focus-visible:ring-gold-400/40"></span>
                        <span>
                            @if ($isArticle)
                                <span class="block text-sm font-medium text-forest-900">Published</span>
                                <span class="block text-sm text-ink-soft">Off = draft. A draft is saved but visitors cannot see it. Use Preview to check it first.</span>
                            @else
                                <span class="block text-sm font-medium text-forest-900">Show on the live site</span>
                                <span class="block text-sm text-ink-soft">Turn off to hide this entry without deleting it.</span>
                            @endif
                        </span>
                    </label>

                    <div>
                        <label for="section" class="field-label">Section</label>
                        <input type="text" id="section" name="section" list="sections" value="{{ old('section', $item->section) }}" required class="field-input">
                        <datalist id="sections">
                            @foreach ($sections as $section)
                                <option value="{{ $section }}"></option>
                            @endforeach
                        </datalist>
                        <p class="field-hint">Entries with the same section appear together, for example "villas" or "wellness".</p>
                    </div>

                    <div>
                        <label for="sort_order" class="field-label">Position</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="field-input">
                        <p class="field-hint">Lower numbers appear first.</p>
                    </div>

                    @if ($show('price'))
                    <div>
                        <label for="price" class="field-label">{{ $label('price', 'Price') }}</label>
                        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $item->price) }}" class="field-input">
                    </div>
                    @endif

                    @if (true) {{-- Every content entry can have its own admin-managed icon or SVG. --}}
                    @php
                        $iconIsImage = (bool) preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', (string) old('icon', $item->icon));
                    @endphp
                    <div>
                        <label for="icon" class="field-label">{{ $label('icon', 'Icon') }}</label>

                        @if ($iconIsImage)
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-14 w-14 items-center justify-center rounded-lg border border-sand-300 bg-sand-100">
                                    <img src="{{ asset(old('icon', $item->icon)) }}" alt="Current icon image" class="h-10 w-10 object-contain">
                                </span>
                                <span class="text-sm text-ink-soft">Current icon image</span>
                            </div>
                        @endif

                        <input type="file" id="icon_image" name="icon_image" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" class="field-input">
                        <p class="field-hint">Upload an icon image (PNG, static SVG, JPG or WebP, up to 2 MB). A square image with a transparent background looks best. It replaces the current icon.</p>

                        <label for="icon" class="field-label mt-4">Or type an emoji / icon name</label>
                        <input type="text" id="icon" name="icon" value="{{ old('icon', $item->icon) }}" placeholder="e.g. 🌿 or lotus" class="field-input">
                        <p class="field-hint">{{ $hint('icon') ?? 'An emoji (🌿) or an icon name (lotus, leaf, island). Upload a file above to replace the icon; clear this box to remove it (when no new file is selected).' }}</p>
                    </div>
                    @endif

                    @if (isset($meta['fields']['perk_icon']))
                    @php
                        $perkIsImage = (bool) preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', (string) old('perk_icon', $item->perk_icon));
                    @endphp
                    <div>
                        <label for="perk_icon" class="field-label">{{ $label('perk_icon', 'Second icon') }}</label>

                        @if ($perkIsImage)
                            <div class="mb-3 flex items-center gap-3">
                                <span class="flex h-14 w-14 items-center justify-center rounded-lg border border-sand-300 bg-sand-100">
                                    <img src="{{ asset(old('perk_icon', $item->perk_icon)) }}" alt="Current second icon image" class="h-10 w-10 object-contain">
                                </span>
                                <span class="text-sm text-ink-soft">Current icon image</span>
                            </div>
                        @endif

                        <input type="file" id="perk_icon_image" name="perk_icon_image" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" class="field-input">
                        <p class="field-hint">Upload an image (PNG, static SVG, JPG or WebP, up to 2 MB). It replaces the current icon.</p>

                        <label for="perk_icon" class="field-label mt-4">Or type an emoji / icon name</label>
                        <input type="text" id="perk_icon" name="perk_icon" value="{{ old('perk_icon', $item->perk_icon) }}" placeholder="e.g. 🏷️ or tag" class="field-input">
                        <p class="field-hint">{{ $hint('perk_icon') ?? 'An emoji or an icon name. Clear this box to go back to the original drawing.' }}</p>
                    </div>
                    @endif
                </div>
            </section>
        </aside>

        <div class="sticky bottom-0 z-10 -mx-4 flex items-center gap-3 border-t border-sand-300 bg-sand-100/95 px-4 py-4 backdrop-blur sm:-mx-8 sm:px-8 lg:-mx-12 lg:col-span-2 lg:px-12">
            <button type="submit" class="btn-primary px-6">{{ $item->exists ? 'Save changes' : 'Create entry' }}</button>
            @if ($isArticle && $item->exists)
                <a href="{{ route('admin.content.preview', $item) }}" target="_blank" rel="noopener" class="btn-secondary">Preview</a>
            @endif
            <a href="{{ route('admin.content.index', $item->section ? ['section' => $item->section] : []) }}" class="btn-secondary">Cancel</a>
            @if ($isArticle && $item->exists)
                <span class="hidden text-sm text-ink-soft sm:inline">Preview shows the last saved version.</span>
            @endif
        </div>
    </form>

    @if ($isArticle)
        @push('scripts')
            @vite('resources/js/admin-editor.js')
        @endpush
    @endif

@endsection
