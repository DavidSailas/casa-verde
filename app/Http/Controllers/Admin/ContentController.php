<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JournalController;
use App\Models\HomeContent;
use App\Support\ImageUploader;
use App\Support\MenuSizes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContentController extends Controller
{
    /**
     * List content entries, filtered by area of the site (group) and/or section.
     */
    public function index(Request $request): View
    {
        $section = $request->query('section');

        $groups = HomeContent::pageGroups();

        // A ?section= link carries its own group so the tabs stay in sync.
        $currentGroup = $groups->get((string) $request->query('group', ''))
            ?? ($section ? $groups->get(HomeContent::groupKeyFor($section)) : null);

        $items = HomeContent::query()
            ->when($section, fn ($query) => $query->where('section', $section))
            ->when(! $section && $currentGroup, fn ($query) => $query->whereIn(
                'section',
                collect($currentGroup['sections'])->pluck('name')
            ))
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get();

        $blocks = $items
            ->groupBy('section')
            ->map(fn ($entries, $name) => [
                'name' => $name,
                'label' => HomeContent::sectionLabel($name),
                'items' => $entries,
            ])
            // Sections described in config/admin.php follow that order (the order
            // they appear on the site); the rest keep their alphabetical order.
            ->sortBy(fn (array $block): int => ($position = array_search($block['name'], array_keys(config('admin.sections', [])), true)) === false
                ? PHP_INT_MAX
                : $position)
            ->values();

        return view('admin.content.index', [
            'blocks' => $blocks,
            'groups' => $groups,
            'currentGroup' => $currentGroup,
            'currentSection' => $section,
            'totalEntries' => $groups->sum('total'),
        ]);
    }

    /**
     * Show the form for creating a new entry.
     */
    public function create(Request $request): View
    {
        $item = new HomeContent([
            'section' => $request->query('section'),
            // A new journal article starts as a draft until it is published.
            'active' => $request->query('section') !== 'journal_articles',
        ]);

        return view('admin.content.form', [
            'item' => $item,
            'sections' => $this->existingSections(),
        ]);
    }

    /**
     * Persist a newly created entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        // An uploaded icon image is stored in the "icon" field as its path.
        if ($request->hasFile('icon_image')) {
            $data['icon'] = $this->storeImage($request, 'icon_image');
        }

        if ($request->hasFile('perk_icon_image')) {
            $data['perk_icon'] = $this->storeImage($request, 'perk_icon_image');
        }

        if (($gallery = $this->gallery($request)) !== null) {
            $data['gallery'] = $gallery;
        }

        if (($details = $this->details($request)) !== null) {
            $data['details'] = $details;
        }

        if (($sizes = $this->sizes($request, (string) ($data['section'] ?? ''), null)) !== null) {
            $data['details'] = $sizes;
        }

        $item = HomeContent::create($data);

        return redirect()
            ->route('admin.content.edit', $item)
            ->with('status', 'Entry created successfully.');
    }

    /**
     * Show the form for editing an entry.
     */
    public function edit(HomeContent $content): View
    {
        return view('admin.content.form', [
            'item' => $content,
            'sections' => $this->existingSections(),
        ]);
    }

    /**
     * Update an existing entry.
     */
    public function update(Request $request, HomeContent $content): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        // An uploaded icon image is stored in the "icon" field as its path.
        if ($request->hasFile('icon_image')) {
            $data['icon'] = $this->storeImage($request, 'icon_image');
        }

        if ($request->hasFile('perk_icon_image')) {
            $data['perk_icon'] = $this->storeImage($request, 'perk_icon_image');
        }

        if (($gallery = $this->gallery($request)) !== null) {
            $data['gallery'] = $gallery;
        }

        if (($details = $this->details($request)) !== null) {
            $data['details'] = $details;
        }

        if (($sizes = $this->sizes($request, (string) ($data['section'] ?? $content->section), $content)) !== null) {
            $data['details'] = $sizes;
        }

        $content->update($data);

        return redirect()
            ->route('admin.content.edit', $content)
            ->with('status', 'Entry updated successfully.');
    }

    /**
     * Save a new order after drag-and-drop: the ids of one section, first to last. Positions become 1, 2, 3...
     * which is how the site reads "sort_order" everywhere.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:300'],
            'ids.*' => ['integer', 'distinct', 'exists:home_contents,id'],
        ]);

        $ids = array_map('intval', array_values($data['ids']));
        $rows = HomeContent::whereIn('id', $ids)->get()->keyBy('id');
        $sections = $rows->pluck('section')->unique();

        // One whole section at a time, so a partial list can never scramble the positions of the rest.
        abort_unless(
            $sections->count() === 1 && HomeContent::where('section', $sections->first())->count() === count($ids),
            422,
            'Send every entry of one section.'
        );

        DB::transaction(function () use ($ids, $rows): void {
            foreach ($ids as $position => $id) {
                $row = $rows[$id];

                if ((int) $row->sort_order !== $position + 1) {
                    $row->update(['sort_order' => $position + 1]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }

    /**
     * Show a journal article as visitors will see it, even while it is still a draft.
     */
    public function preview(HomeContent $content): View|RedirectResponse
    {
        abort_unless($content->section === 'journal_articles', 404);

        if (trim((string) $content->title) === '') {
            return redirect()
                ->route('admin.content.edit', $content)
                ->withErrors(['title' => 'Give the article a title before you preview it.']);
        }

        return app(JournalController::class)->preview($content);
    }

    /**
     * Delete an entry.
     */
    public function destroy(HomeContent $content): RedirectResponse
    {
        $section = $content->section;
        $content->delete();

        return redirect()
            ->route('admin.content.index', ['section' => $section])
            ->with('status', 'Entry deleted.');
    }

    /**
     * Validate the shared create/update payload.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'section' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'perk_icon' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric'],
            'features' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'icon_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,gif,svg', 'max:2048'],
            'perk_icon_image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,gif,svg', 'max:2048'],
            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => ['string', 'max:255'],
            'gallery_new' => ['nullable', 'array', 'max:20'],
            'gallery_new.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string', 'max:20'],
            'details' => ['nullable', 'array'],
            'details.intro' => ['nullable', 'string', 'max:2000'],
            'details.story_title' => ['nullable', 'string', 'max:300'],
            'details.story_text' => ['nullable', 'string', 'max:4000'],
            'details.highlights' => ['nullable', 'array', 'max:8'],
            'details.highlights.*.icon' => ['nullable', 'string', 'max:255'],
            'details.highlights.*.icon2' => ['nullable', 'string', 'max:255'],
            'details.highlights.*.label' => ['nullable', 'string', 'max:120'],
            'details.amenities' => ['nullable', 'array', 'max:3'],
            'details.amenities.*' => ['nullable', 'string', 'max:2000'],
            'details.capacity.guests' => ['nullable', 'integer', 'min:0', 'max:99'],
            'details.capacity.bathrooms' => ['nullable', 'integer', 'min:0', 'max:99'],
            'details.bedrooms' => ['nullable', 'array', 'max:8'],
            'details.bedrooms.*.name' => ['nullable', 'string', 'max:80'],
            'details.bedrooms.*.beds' => ['nullable', 'string', 'max:120'],
            'details.layout.interior' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'details.layout.terrace' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'details.hero' => ['nullable', 'string', 'max:255'],
            'details.plan' => ['nullable', 'string', 'max:255'],
            'detail_hero' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'detail_plan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Titles and text can contain deliberate line breaks (the site renders
        // them with nl2br). Textareas submit Windows line endings, so store
        // plain "\n" like the rest of the content.
        foreach (['title', 'subtitle', 'description'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = str_replace(["\r\n", "\r"], "\n", $data[$field]);
            }
        }

        // "features" is submitted as one item per line in a textarea; the model
        // stores it as a JSON array. Only touch it when the field was actually
        // submitted, so a partial save can never wipe it.
        if ($request->has('features')) {
            $data['features'] = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('features')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();
        } else {
            unset($data['features']);
        }

        // Same rule for visibility and ordering: never reset them silently.
        if ($request->has('active')) {
            $data['active'] = $request->boolean('active');
        }

        if ($request->filled('sort_order')) {
            $data['sort_order'] = (int) $request->input('sort_order');
        } else {
            unset($data['sort_order']);
        }

        unset($data['image'], $data['icon_image'], $data['perk_icon_image'], $data['gallery'], $data['gallery_new'], $data['details'], $data['sizes'], $data['detail_hero'], $data['detail_plan']);

        return $data;
    }

    /**
     * Move the uploaded image into public/images and return its stored path,
     * matching the "images/filename.ext" convention already used across the site.
     */
    protected function storeImage(Request $request, string $field = 'image'): string
    {
        return $this->storeFile($request->file($field), $field, MenuSizes::uploadLimit($request->input('section'), $field));
    }

    /**
     * The "Display size" choices of a menu entry (food menu book, drinks menu, a dish), merged into its saved details so
     * nothing else stored there is lost. Null when the form had no such box or the section has no size settings.
     *
     * @return array<string, mixed>|null
     */
    protected function sizes(Request $request, string $section, ?HomeContent $existing): ?array
    {
        if (! $request->boolean('sizes_present')
            || MenuSizes::settingsFor($section) === null
            || ! \Illuminate\Support\Facades\Schema::hasColumn('home_contents', 'details')) {
            return null;
        }

        $details = (array) ($existing?->details ?? []);
        $details['display'] = MenuSizes::clean($section, (array) $request->input('sizes', []));

        return $details;
    }

    /**
     * The photos of an entry's gallery, in order: the ones kept from before (in the order the
     * form lists them) followed by newly uploaded ones. Null when the form had no gallery box,
     * so saving any other entry can never touch a gallery.
     *
     * @return array<int, string>|null
     */
    protected function gallery(Request $request): ?array
    {
        if (! $request->boolean('gallery_present')
            || ! \Illuminate\Support\Facades\Schema::hasColumn('home_contents', 'gallery')) {
            return null;
        }

        $kept = collect((array) $request->input('gallery', []))
            ->filter(fn ($path) => is_string($path)
                && ! str_contains($path, '..')
                && preg_match('#^images/[A-Za-z0-9._/-]+\.(jpe?g|png|webp|gif|svg)$#i', $path)
                && file_exists(public_path($path)))
            ->unique()
            ->values()
            ->all();

        $added = [];

        foreach ((array) $request->file('gallery_new', []) as $file) {
            if ($file && $file->isValid()) {
                $added[] = $this->storeFile($file, 'gallery_new');
            }
        }

        return array_slice(array_merge($kept, $added), 0, 30);
    }

    /**
     * The detail-page content of a villa (see App\Support\VillaDetails), from the "Villa detail page"
     * box of the form. Null when the form had no such box, so saving any other entry can never touch it.
     *
     * @return array<string, mixed>|null
     */
    protected function details(Request $request): ?array
    {
        if (! $request->boolean('details_present')
            || ! \Illuminate\Support\Facades\Schema::hasColumn('home_contents', 'details')) {
            return null;
        }

        $in = (array) $request->input('details', []);
        $clean = fn ($value): string => trim(str_replace(["\r\n", "\r"], "\n", (string) $value));

        // A picture already on the site is kept unless "remove" is ticked or a new file is uploaded.
        $picture = function (string $key, string $upload, string $remove) use ($request, $in): ?string {
            if ($request->hasFile($upload)) {
                return $this->storeFile($request->file($upload), $upload);
            }

            $path = $in[$key] ?? null;

            return ! $request->boolean($remove)
                && is_string($path)
                && ! str_contains($path, '..')
                && preg_match('#^images/[A-Za-z0-9._/-]+\.(jpe?g|png|webp|gif)$#i', $path)
                && file_exists(public_path($path)) ? $path : null;
        };

        $highlights = collect((array) ($in['highlights'] ?? []))
            ->filter(fn ($row) => is_array($row) && filled(trim((string) ($row['label'] ?? ''))))
            ->map(fn (array $row) => [
                'icon' => trim((string) ($row['icon'] ?? '')),
                'icon2' => trim((string) ($row['icon2'] ?? '')),
                'label' => trim((string) $row['label']),
            ])
            ->take(8)
            ->values()
            ->all();

        $bedrooms = collect((array) ($in['bedrooms'] ?? []))
            ->filter(fn ($row) => is_array($row) && (filled(trim((string) ($row['name'] ?? ''))) || filled(trim((string) ($row['beds'] ?? '')))))
            ->map(fn (array $row) => [
                'name' => trim((string) ($row['name'] ?? '')),
                'beds' => trim((string) ($row['beds'] ?? '')),
            ])
            ->take(8)
            ->values()
            ->all();

        return [
            'intro' => $clean($in['intro'] ?? ''),
            'story_title' => $clean($in['story_title'] ?? ''),
            'story_text' => $clean($in['story_text'] ?? ''),
            'highlights' => $highlights,
            'capacity' => [
                'guests' => $in['capacity']['guests'] ?? '',
                'bathrooms' => $in['capacity']['bathrooms'] ?? '',
            ],
            'bedrooms' => $bedrooms,
            'amenities' => collect(range(0, 2))->map(fn (int $i) => $clean($in['amenities'][$i] ?? ''))->all(),
            'layout' => [
                'interior' => $in['layout']['interior'] ?? '',
                'terrace' => $in['layout']['terrace'] ?? '',
            ],
            'hero' => $picture('hero', 'detail_hero', 'remove_detail_hero'),
            'plan' => $picture('plan', 'detail_plan', 'remove_detail_plan'),
        ];
    }

    protected function storeFile(\Illuminate\Http\UploadedFile $file, string $field, ?int $maxSide = null): string
    {
        if (strtolower($file->getClientOriginalExtension()) === 'svg') {
            $svg = file_get_contents($file->getRealPath());
            if (! preg_match('/<svg\b/i', $svg) || preg_match('/<\s*(script|foreignObject|iframe|object|embed|use|image|animate|set)\b|\bon[a-z]+\s*=|(?:href|src)\s*=|javascript:|data:|<!DOCTYPE|<!ENTITY|<\?xml-stylesheet/i', $svg)) {
                throw \Illuminate\Validation\ValidationException::withMessages([$field => 'Upload a static SVG icon without scripts, links, external resources or animations.']);
            }
        }

        return ImageUploader::store($file, $maxSide);
    }

    /**
     * All distinct section names currently in use, for the section datalist.
     */
    protected function existingSections(): \Illuminate\Support\Collection
    {
        return HomeContent::query()
            ->select('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section');
    }
}
