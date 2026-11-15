<?php

namespace App\Http\Controllers;

use App\Models\HomeContent;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class YogaController extends Controller
{
    /**
     * Every "yoga_*" row grouped by section, including entries that are switched off, so an entry
     * that is hidden in the admin stays hidden instead of falling back to the starting content.
     *
     * @var Collection<string, Collection<int, HomeContent>>
     */
    protected Collection $rows;

    /**
     * The Yoga Reset content (admin entries over the starting content), so the booking page
     * can show the same classes, prices and photos as this page.
     *
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $this->rows = HomeContent::where('section', 'like', 'yoga_%')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (HomeContent $r): bool => str_starts_with((string) $r->section, 'yoga_'))
            ->groupBy('section');

        return $this->buildPage(config('yoga'));
    }

    public function index(): View
    {
        $contents = HomeContent::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $site = $contents->get('site', collect())->first();

        // "yoga_%" is only a prefilter (in LIKE the underscore matches any character, and SQLite has no
        // default escape character), so the exact prefix is checked again in PHP.
        $this->rows = HomeContent::where('section', 'like', 'yoga_%')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (HomeContent $r): bool => str_starts_with((string) $r->section, 'yoga_'))
            ->groupBy('section');

        $page = $this->buildPage(config('yoga'));

        return view('yoga', compact('contents', 'site', 'page'));
    }

    /**
     * The page, built from the admin entries with the starting content as fallback.
     *
     * @param  array<string, mixed>  $d  the defaults (config/yoga.php)
     * @return array<string, mixed>
     */
    protected function buildPage(array $d): array
    {
        $scheduleItems = $this->all('yoga_schedule', $d['schedule']['items'], fn (HomeContent $r): array => [
            'time' => $r->title,
            'name' => $r->subtitle,
            'text' => $r->description,
            // The "day" an entry belongs to (1 = first day tab). Empty: shown on every day.
            'day' => filled($r->price) ? (int) $r->price : null,
        ], fn (array $x): array => [
            'time' => $x['title'], 'name' => $x['eyebrow'], 'text' => $x['text'] ?? null, 'day' => null,
        ]);

        $days = collect($this->all('yoga_days', $d['days'], fn (HomeContent $r): array => [
            'name' => $r->title,
            'heading' => $r->description,
        ], fn (array $x): array => $x))
            ->values()
            ->map(fn (array $day, int $i): array => $day + ['n' => $i + 1, 'label' => 'Day '.($i + 1)])
            ->all();

        $scheduleHeader = $this->one('yoga_schedule_header', null, fn (HomeContent $r): array => [
            'eyebrow' => $r->subtitle, 'title' => $r->title,
        ]) ?? ['eyebrow' => $d['schedule']['eyebrow'], 'title' => $d['schedule']['title']];

        $page = [
            'hero' => $this->single('yoga_hero', $d['hero']),
            'hero_link' => $this->one('yoga_hero_link', $d['hero_link'], fn (HomeContent $r): ?array => filled($r->button_text)
                ? ['text' => $r->button_text, 'url' => $r->button_url ?: $d['hero_link']['url']]
                : null),
            'chips' => $this->all('yoga_hero_chips', $d['chips'], fn (HomeContent $r): array => [
                'text' => $r->title,
                'style' => in_array($r->subtitle, ['light', 'warm'], true) ? $r->subtitle : 'dark',
            ], fn (array $x): array => $x),
            'facts' => array_column($this->all('yoga_hero_facts', $d['facts'], fn (HomeContent $r): array => [
                'text' => $r->title,
            ], fn (string $x): array => ['text' => $x]), 'text'),

            'notice' => $this->one('yoga_notice', $d['notice'], fn (HomeContent $r): array => [
                'label' => $r->subtitle, 'title' => $r->title, 'text' => $r->description,
            ]),

            'intro' => $this->single('yoga_intro', $d['intro']),
            'cards' => array_map(fn (array $type): array => $this->withDetails($type), $this->all('yoga_types', $d['types'], fn (HomeContent $r): array => $this->fromRow($r, []), fn (array $x): array => $x)),

            'programme' => [
                'header' => $scheduleHeader,
                'stats' => $this->all('yoga_stats', $d['stats'], fn (HomeContent $r): array => $this->fromRow($r, []), fn (array $x): array => $x),
                'flow' => $this->one('yoga_flow', $d['flow'], fn (HomeContent $r): array => [
                    'title' => $r->title, 'note' => $r->subtitle, 'text' => $r->description,
                ]),
                'days' => $days,
                'panels' => $this->panels($scheduleItems, $days),
                'care' => $this->one('yoga_care', $d['care'], fn (HomeContent $r): array => [
                    'title' => $r->title, 'items' => $r->features ?? [], 'note' => $r->description,
                ]),
            ],

            'classes' => [
                'info' => $this->one('yoga_classes_info', [
                    'title' => $d['personal']['title'],
                    'text' => $d['personal']['text'],
                    'note' => $d['classes']['note'],
                    'small' => $d['classes']['small'],
                ], fn (HomeContent $r): array => [
                    'title' => $r->title, 'text' => $r->description, 'note' => $r->subtitle, 'small' => ($r->features ?? [])[0] ?? null,
                ]),
                'groups' => $this->groups($this->all('yoga_classes', $d['classes']['sessions'], fn (HomeContent $r): array => [
                    'group' => $r->title,
                    'name' => $r->subtitle,
                    'meta' => $r->description,
                    'button_text' => $r->button_text,
                    'button_url' => $r->button_url ?: '/#contact',
                ], fn (array $x): array => $x)),
            ],

            'resort' => [
                'text' => $this->one('yoga_resort', $d['resort'], fn (HomeContent $r): array => [
                    'title' => $r->title, 'text' => $r->description,
                ]),
                'list_title' => $this->one('yoga_features_header', ['title' => $d['features']['title']], fn (HomeContent $r): array => ['title' => $r->title])['title'] ?? null,
                'list' => $this->all('yoga_features', $d['features']['items'], fn (HomeContent $r): array => $this->fromRow($r, []), fn (array $x): array => $x),
                'photos' => $this->all('yoga_resort_photos', $d['resort']['photos'], fn (HomeContent $r): array => [
                    'image' => $r->image, 'caption' => $r->title,
                ], fn (array $x): array => $x),
                'spotlight' => $this->one('yoga_spotlight', $d['resort']['spotlight'], fn (HomeContent $r): array => [
                    'image' => $r->image, 'badge' => $r->subtitle, 'title' => $r->title, 'text' => $r->description,
                ]),
            ],

            'gallery' => [
                'head' => $this->one('yoga_gallery_header', ['title' => $d['gallery']['title'], 'text' => $d['gallery']['text']], fn (HomeContent $r): array => [
                    'title' => $r->title, 'text' => $r->description,
                ]),
                'items' => array_values(array_filter($this->all('yoga_gallery', $d['gallery']['items'], fn (HomeContent $r): array => [
                    'image' => $r->image, 'caption' => $r->title,
                ], fn (array $x): array => $x), fn (array $x): bool => filled($x['image']))),
            ],

            'teacher' => $this->one('yoga_teacher', $d['teacher'], fn (HomeContent $r): array => [
                'title' => $r->title, 'role' => $r->subtitle, 'text' => $r->description,
                'image' => $r->image, 'caption' => $r->button_text, 'styles' => $r->features ?? [],
            ]),
            'extra' => $this->one('yoga_extra', null, fn (HomeContent $r): array => [
                'badge' => $r->subtitle, 'title' => $r->title, 'text' => $r->description, 'image' => $r->image,
                'gold' => ($r->features ?? [])[0] ?? null, 'button_text' => $r->button_text, 'button_url' => $r->button_url ?: '/#contact',
            ]),

            'package' => [
                'title' => $this->one('yoga_package_header', ['title' => $d['package']['title']], fn (HomeContent $r): array => ['title' => $r->title])['title'] ?? null,
                'columns' => $this->all('yoga_package', $d['package']['columns'], fn (HomeContent $r): array => [
                    'title' => $r->title,
                    'style' => $r->subtitle === 'extras' ? 'extras' : 'included',
                    'items' => $r->features ?? [],
                ], fn (array $x): array => $x),
                'price' => $this->one('yoga_price', $d['package']['price'], fn (HomeContent $r): array => [
                    'title' => $r->title, 'big' => $r->subtitle, 'text' => $r->description, 'bullets' => $r->features ?? [],
                    'button_text' => $r->button_text, 'button_url' => $r->button_url ?: '/#contact',
                ]),
                'next' => $this->one('yoga_next', null, fn (HomeContent $r): array => [
                    'title' => $r->title, 'text' => $r->description, 'button_text' => $r->button_text, 'button_url' => $r->button_url ?: '/#contact',
                ]),
                'share' => $this->one('yoga_share', null, fn (HomeContent $r): array => [
                    'title' => $r->title, 'text' => $r->description, 'button_text' => $r->button_text, 'button_url' => $r->button_url ?: '#',
                ]),
            ],

            'faq' => [
                'title' => $this->one('yoga_faq_header', ['title' => $d['faq']['title']], fn (HomeContent $r): array => ['title' => $r->title])['title'] ?? $d['faq']['title'],
                'items' => $this->all('yoga_faq', $d['faq']['items'], fn (HomeContent $r): array => $this->fromRow($r, []), fn (array $x): array => $x),
            ],
            'quote' => $this->single('yoga_quote', $d['quote']),
            'cta' => $this->callToAction($d['cta']),
        ];

        $page['faq']['items'] = array_values(array_filter($page['faq']['items'], fn (array $x): bool => filled($x['title']) && filled($x['text'])));
        $page['layout'] = $this->layout($page, $d['layout']);

        // Where a heading has no small label of its own, the block name is used.
        $page['intro']['eyebrow'] = $page['intro']['eyebrow'] ?: ($page['layout']['labels']['about'] ?? null);
        $page['programme']['header']['eyebrow'] = $page['programme']['header']['eyebrow'] ?: ($page['layout']['labels']['programme'] ?? null);

        return $page;
    }

    /**
     * Which blocks are shown, in which order, and the label above each. Entries in the admin
     * (Yoga Reset > Page layout) decide; blocks without an entry still show, at the end.
     *
     * @param  array<string, mixed>  $page
     * @param  array<string, array<string, string>>  $definitions
     * @return array{flow: array<int, string>, labels: array<string, string>}
     */
    protected function layout(array $page, array $definitions): array
    {
        $blocks = ($this->rows->get('yoga_layout') ?? collect())
            ->filter(fn (HomeContent $r): bool => array_key_exists((string) $r->subtitle, $definitions))
            ->unique(fn (HomeContent $r): string => (string) $r->subtitle)
            ->map(fn (HomeContent $r): array => [
                'key' => (string) $r->subtitle,
                'label' => filled($r->title) ? trim((string) $r->title) : $definitions[$r->subtitle]['title'],
                'visible' => (bool) $r->active,
            ])
            ->values();

        foreach ($definitions as $key => $definition) {
            if (! $blocks->contains('key', $key)) {
                $blocks->push(['key' => $key, 'label' => $definition['title'], 'visible' => true]);
            }
        }

        $has = [
            'notice' => $page['notice'] !== null,
            'about' => true,
            'programme' => count($page['programme']['stats']) + count($page['programme']['panels'][0]['items'] ?? []) > 0,
            'classes' => count($page['classes']['groups']) > 0,
            'resort' => $page['resort']['text'] !== null || count($page['resort']['photos']) > 0 || count($page['resort']['list']) > 0,
            'gallery' => count($page['gallery']['items']) > 0,
            'teacher' => $page['teacher'] !== null,
            'package' => count($page['package']['columns']) > 0 || $page['package']['price'] !== null,
            'faq' => count($page['faq']['items']) > 0,
            'quote' => filled($page['quote']['text'] ?? null),
            'cta' => true,
        ];

        $shown = $blocks->filter(fn (array $b): bool => $b['visible'] && ($has[$b['key']] ?? false));

        return [
            'flow' => $shown->pluck('key')->values()->all(),
            'labels' => $blocks->pluck('label', 'key')->all(),
        ];
    }

    /**
     * The timeline panels: one per day tab, or a single one when no days are defined. An entry with
     * no day number appears on every day.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $days
     * @return array<int, array<string, mixed>>
     */
    protected function panels(array $items, array $days): array
    {
        if ($days === []) {
            return [['n' => 1, 'name' => null, 'heading' => null, 'label' => null, 'items' => $items]];
        }

        return array_map(fn (array $day): array => $day + [
            'items' => array_values(array_filter($items, fn (array $i): bool => $i['day'] === null || $i['day'] === $day['n'])),
        ], $days);
    }

    /**
     * Sessions grouped by their day label, in the order they were entered.
     *
     * @param  array<int, array<string, mixed>>  $sessions
     * @return array<int, array{label: string, sessions: array<int, array<string, mixed>>}>
     */
    protected function groups(array $sessions): array
    {
        $groups = [];

        foreach ($sessions as $session) {
            $label = (string) $session['group'];
            $groups[$label] ??= ['label' => $label, 'sessions' => []];
            $groups[$label]['sessions'][] = $session;
        }

        return array_values($groups);
    }

    /**
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    protected function callToAction(array $default): array
    {
        $rows = ($this->rows->get('yoga_cta') ?? collect())->filter(fn (HomeContent $r): bool => (bool) $r->active)->values();

        if ($rows->isEmpty()) {
            return $default;
        }

        return [
            'title' => $rows->first()->title ?: $default['title'],
            'image' => $rows->first()->image ?: $default['image'],
            'buttons' => $rows
                ->filter(fn (HomeContent $r): bool => filled($r->button_text))
                ->map(fn (HomeContent $r): array => ['text' => $r->button_text, 'url' => $r->button_url ?: '/#contact'])
                ->values()
                ->all(),
        ];
    }

    /**
     * Split a session's "6:00 AM · 60 min · All levels" details into a badge (the first part) and tags.
     *
     * @param  array<string, mixed>  $type
     * @return array<string, mixed>
     */
    protected function withDetails(array $type): array
    {
        $parts = array_values(array_filter(array_map('trim', preg_split('/[·|]/u', (string) ($type['eyebrow'] ?? '')))));

        $type['badge'] = $parts[0] ?? null;
        $type['chips'] = array_slice($parts, 1);
        $type['meta'] = implode(' · ', $parts);

        return $type;
    }

    /**
     * Admin entries of a section as a list. With no entries at all the starting content shows;
     * entries that are all switched off give an empty list.
     *
     * @param  array<int, mixed>  $default
     * @return array<int, array<string, mixed>>
     */
    protected function all(string $section, array $default, callable $fromRow, callable $fromDefault): array
    {
        $rows = $this->rows->get($section);

        if ($rows === null) {
            return array_map($fromDefault, $default);
        }

        return $rows->filter(fn (HomeContent $r): bool => (bool) $r->active)->map($fromRow)->values()->all();
    }

    /**
     * A one-entry section: its content, null when switched off, or the starting content when it has no entry.
     *
     * @param  array<string, mixed>|null  $default
     * @return array<string, mixed>|null
     */
    protected function one(string $section, ?array $default, callable $fromRow): ?array
    {
        $row = $this->rows->get($section)?->first();

        if ($row === null) {
            return $default === null ? null : $this->withParagraphs($default);
        }

        $mapped = $row->active ? $fromRow($row) : null;

        return $mapped === null ? null : $this->withParagraphs($mapped);
    }

    /**
     * A section whose entry always shows, falling back field by field to the starting content.
     *
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    protected function single(string $section, array $default): array
    {
        $row = $this->rows->get($section)?->first(fn (HomeContent $r): bool => (bool) $r->active);

        return $this->withParagraphs($row ? $this->fromRow($row, $default) : $default);
    }

    /**
     * Long text is shown as its first paragraph with the rest behind "Read more".
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withParagraphs(array $data): array
    {
        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/u', (string) ($data['text'] ?? '')))));

        $data['lead'] = $paragraphs[0] ?? null;
        $data['more'] = array_slice($paragraphs, 1);

        return $data;
    }

    /**
     * Map an admin row onto the page fields, falling back to the defaults for blanks.
     *
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    protected function fromRow(HomeContent $row, array $defaults): array
    {
        return [
            'eyebrow' => $row->subtitle ?: ($defaults['eyebrow'] ?? null),
            'title' => $row->title ?: ($defaults['title'] ?? null),
            'text' => $row->description ?: ($defaults['text'] ?? null),
            'image' => $row->image ?: ($defaults['image'] ?? null),
            'icon' => $row->icon ?: ($defaults['icon'] ?? null),
            'button_text' => $row->button_text ?: ($defaults['button_text'] ?? null),
            'button_url' => $row->button_url ?: ($defaults['button_url'] ?? '/#contact'),
        ];
    }
}
