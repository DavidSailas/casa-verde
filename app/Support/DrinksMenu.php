<?php

namespace App\Support;

use App\Models\HomeContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The drinks menu opened by the EXPLORE DRINKS MENU button on the Dining page, built from the admin:
 * its settings (title, line under the title, note, currency), categories, drinks (name, price, text,
 * photo, tags) and the filter buttons. A new category or drink simply appears in the menu.
 *
 * Unlike the food menu there is no printed fallback: the menu is exactly what is saved (and visible)
 * in the admin, so hiding or deleting every drink turns the menu off.
 */
class DrinksMenu
{
    /** Category layouts offered in the admin: value => label. An empty layout is "automatic". */
    public const LAYOUTS = [
        'cards' => 'Photo cards (a large photo for each drink)',
        'list' => 'Compact list (name, price and text)',
    ];

    /** Name of the category that collects drinks whose category is missing or was deleted. */
    public const OTHER_CATEGORY = 'More drinks';

    /**
     * Everything the drinks menu needs.
     *
     * Each category is ['name' => string, 'slug' => string, 'style' => 'cards'|'list',
     * 'items' => [['name' => string, 'price' => string, 'image' => ?string, 'text' => string, 'tags' => string[]]]].
     * Categories without a visible drink are left out, and so are filters that no drink uses.
     *
     * @param  Collection<string, Collection<int, HomeContent>>  $contents  active rows grouped by section
     * @return array{title: string, brand: string, logo: string, currency: string, note: string, style: string, filters: array<int, string>, categories: array<int, array<string, mixed>>, total: int}
     */
    public static function data(Collection $contents): array
    {
        $settings = HomeContent::pageRow($contents, 'dining_page_drinks_menu');

        $categories = static::rows($contents, 'dining_page_drinks_categories')
            ->filter(fn (HomeContent $row): bool => trim((string) $row->title) !== '')
            ->values();

        $byCategory = [];
        $loose = [];

        foreach (static::rows($contents, 'dining_page_drinks_items') as $drink) {
            if (trim((string) $drink->title) === '') {
                continue;
            }

            $index = $categories->search(fn (HomeContent $category): bool => static::belongsTo($drink, $category));

            if ($index === false) {
                $loose[] = static::drink($drink);
            } else {
                $byCategory[$index][] = static::drink($drink);
            }
        }

        $groups = [];

        foreach ($categories as $index => $category) {
            $groups[] = [
                'name' => trim((string) $category->title),
                'layout' => strtolower(trim((string) $category->subtitle)),
                'items' => $byCategory[$index] ?? [],
            ];
        }

        // A drink never disappears because its category was deleted or switched off.
        $groups[] = ['name' => self::OTHER_CATEGORY, 'layout' => '', 'items' => $loose];

        $slugs = [];
        $result = [];

        foreach ($groups as $group) {
            if ($group['items'] === []) {
                continue;
            }

            $result[] = [
                'name' => $group['name'],
                'slug' => static::uniqueSlug($group['name'], $slugs),
                'style' => static::style($group['layout'], $group['items']),
                'items' => $group['items'],
            ];
        }

        $tagSlugs = collect($result)->pluck('items')->flatten(1)->pluck('tags')->flatten()
            ->map(fn ($tag): string => Str::slug((string) $tag))
            ->filter()
            ->unique();

        $filters = static::rows($contents, 'dining_page_drinks_filters')
            ->map(fn (HomeContent $row): string => trim((string) $row->title))
            ->filter(fn (string $label): bool => $label !== '' && $tagSlugs->contains(Str::slug($label)))
            ->unique(fn (string $label): string => Str::slug($label))
            ->values()
            ->all();

        return [
            'title' => static::text($settings?->title, 'Drinks Menu'),
            'brand' => HomeContent::siteBrand()['name'],
            'logo' => HomeContent::siteBrand()['logo'],
            'currency' => static::text($settings?->button_text, 'PHP'),
            'note' => static::text($settings?->description, ''),
            'filters' => $filters,
            'style' => MenuSizes::drinksStyle($settings),
            'categories' => $result,
            'total' => array_sum(array_map(fn (array $category): int => count($category['items']), $result)),
        ];
    }

    /**
     * Saved, visible entries of a section. No starting content is used here (see the class note).
     *
     * @param  Collection<string, Collection<int, HomeContent>>  $contents
     * @return Collection<int, HomeContent>
     */
    protected static function rows(Collection $contents, string $section): Collection
    {
        return $contents->get($section, collect())->values();
    }

    /** A drink belongs to a category through the category's id (saved in the admin) or its name. */
    protected static function belongsTo(HomeContent $drink, HomeContent $category): bool
    {
        $link = trim((string) $drink->subtitle);

        if ($link === '') {
            return false;
        }

        return ($category->id !== null && $link === (string) $category->id)
            || strcasecmp($link, trim((string) $category->title)) === 0;
    }

    /**
     * Photo cards when the category says so, a compact list when it says so, and otherwise (automatic)
     * photo cards only when every drink in it has a photo, so adding pictures is all it takes.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected static function style(string $layout, array $items): string
    {
        if (in_array($layout, ['cards', 'list'], true)) {
            return $layout;
        }

        return collect($items)->every(fn (array $item): bool => $item['image'] !== null) ? 'cards' : 'list';
    }

    /**
     * @param  array<string, true>  $taken  slugs already used, so two categories never share an element id
     */
    protected static function uniqueSlug(string $name, array &$taken): string
    {
        $base = Str::slug($name) ?: 'section';
        $slug = $base;

        for ($number = 2; isset($taken[$slug]); $number++) {
            $slug = $base.'-'.$number;
        }

        $taken[$slug] = true;

        return $slug;
    }

    protected static function text(mixed $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }

    /** @return array{name: string, price: string, image: ?string, text: string, tags: array<int, string>} */
    protected static function drink(HomeContent $drink): array
    {
        $price = $drink->price;

        if (is_numeric($price)) {
            $price = (float) $price == floor((float) $price)
                ? number_format((float) $price, 0, '.', ',')
                : number_format((float) $price, 2, '.', ',');
        }

        $image = filled($drink->image) && file_exists(public_path((string) $drink->image)) ? (string) $drink->image : null;

        return [
            'name' => trim((string) $drink->title),
            'price' => $price === null || $price === '' ? '' : (string) $price,
            'image' => $image,
            'text' => trim((string) $drink->description),
            'tags' => collect((array) $drink->features)
                ->map(fn ($tag): string => trim((string) $tag))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
