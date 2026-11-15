<?php

namespace App\Support;

use App\Models\HomeContent;

/**
 * How big the photos and icons of the food menu book and the drinks menu are shown.
 *
 * The owner picks a ready-made size (Small, Medium, Large...) in the admin instead of typing pixels, so a
 * page can never be made to overflow. The choice is saved in the "details" of the menu's settings entry
 * (Dining > Food menu book: cover, Dining > Drinks menu: title and notes) and of each dish (its own photo size).
 *
 * Medium (the printed design) is the default, and it adds nothing to the page, so nothing changes until the owner picks another size.
 */
class MenuSizes
{
    public const FOOD = 'dining_page_food_menu';

    public const DRINKS = 'dining_page_drinks_menu';

    public const DISH = 'dining_page_food_menu_dishes';

    /** Food menu book: what can be sized. key => [label, help, options (value => [label, factor])]. */
    public const FOOD_SETTINGS = [
        'photo' => [
            'label' => 'Dish photos',
            'help' => 'How large the round dish photos are on every page of the book.',
            'default' => 'medium',
            'options' => ['small' => ['Small', 0.85], 'medium' => ['Medium', 1.0], 'large' => ['Large', 1.15]],
        ],
        'icon' => [
            'label' => 'Dietary icons next to a dish',
            'help' => 'The small icons (seafood, chili, nuts...) after the dish name.',
            'default' => 'medium',
            'options' => ['small' => ['Small', 0.8], 'medium' => ['Medium', 1.0], 'large' => ['Large', 1.25]],
        ],
        'legend' => [
            'label' => 'Icon legend at the bottom of a page',
            'help' => 'The row of icons with their names under the dishes.',
            'default' => 'medium',
            'options' => ['small' => ['Small', 0.8], 'medium' => ['Medium', 1.0], 'large' => ['Large', 1.25]],
        ],
    ];

    /** Drinks menu: photo cards are shown in this many columns on a large screen (phones and tablets always adapt). */
    public const DRINKS_SETTINGS = [
        'photo' => [
            'label' => 'Drink photos',
            'help' => 'Compact shows four small cards in a row, Large shows two big ones. Phones and tablets always fit the screen.',
            'default' => 'standard',
            'options' => ['compact' => ['Compact', 4], 'standard' => ['Standard', 3], 'large' => ['Large', 2]],
        ],
        'logo' => [
            'label' => 'Logo in the header',
            'help' => 'The logo at the top left of the drinks menu.',
            'default' => 'medium',
            'options' => ['small' => ['Small', 0.8], 'medium' => ['Medium', 1.0], 'large' => ['Large', 1.3]],
        ],
    ];

    /** A single dish: its photo relative to the menu setting. */
    public const DISH_SETTINGS = [
        'photo' => [
            'label' => 'Photo size of this dish',
            'help' => 'Automatic follows the size chosen for the whole menu. Use Smaller or Larger only for a photo that does not look right.',
            'default' => 'auto',
            'options' => ['smaller' => ['Smaller', 0.85], 'auto' => ['Automatic', 1.0], 'larger' => ['Larger', 1.15]],
        ],
    ];

    /** Largest total photo scale (menu size x dish size) so a page with many dishes keeps room for its text. */
    private const MAX_PHOTO_SCALE = 1.3;

    /** The settings that exist for a section, or null when the section has no size settings. */
    public static function settingsFor(?string $section): ?array
    {
        return match ($section) {
            self::FOOD => self::FOOD_SETTINGS,
            self::DRINKS => self::DRINKS_SETTINGS,
            self::DISH => self::DISH_SETTINGS,
            default => null,
        };
    }

    /**
     * Keeps only known settings with known values. Unknown input is dropped, so nothing else can be saved here.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public static function clean(string $section, array $input): array
    {
        $clean = [];

        foreach (self::settingsFor($section) ?? [] as $key => $setting) {
            $value = $input[$key] ?? null;

            if (is_string($value) && isset($setting['options'][$value])) {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    /** The saved choice for a setting, or its default. */
    public static function choice(?HomeContent $row, string $section, string $key): string
    {
        $setting = self::settingsFor($section)[$key] ?? null;
        $saved = $row?->details['display'][$key] ?? null;

        return is_string($saved) && isset($setting['options'][$saved]) ? $saved : (string) ($setting['default'] ?? '');
    }

    /** The number behind a choice (a scale such as 1.15, or a column count for the drinks photos). */
    public static function value(?HomeContent $row, string $section, string $key): float|int
    {
        $choice = self::choice($row, $section, $key);

        return self::settingsFor($section)[$key]['options'][$choice][1] ?? 1.0;
    }

    /** Photo scale of one dish: the menu setting times the dish's own setting, within safe limits. */
    public static function dishPhotoScale(?HomeContent $cover, HomeContent $dish): float
    {
        $scale = self::value($cover, self::FOOD, 'photo') * self::value($dish, self::DISH, 'photo');

        return round(max(0.7, min(self::MAX_PHOTO_SCALE, $scale)), 3);
    }

    /** CSS variables for the pages of the food menu book (icons and legend); empty when everything is Medium. */
    public static function foodStyle(?HomeContent $cover): string
    {
        return self::style([
            '--mb-icon-scale' => self::value($cover, self::FOOD, 'icon'),
            '--mb-legend-scale' => self::value($cover, self::FOOD, 'legend'),
        ]);
    }

    /** CSS variables for the drinks menu; empty when everything is standard. */
    public static function drinksStyle(?HomeContent $settings): string
    {
        return self::style([
            '--dm-cols' => self::value($settings, self::DRINKS, 'photo'),
            '--dm-logo-scale' => self::value($settings, self::DRINKS, 'logo'),
        ], ['--dm-cols' => 3]);
    }

    /**
     * @param  array<string, float|int>  $variables
     * @param  array<string, float|int>  $defaults  values that are left out because the stylesheet already has them
     */
    private static function style(array $variables, array $defaults = []): string
    {
        $parts = [];

        foreach ($variables as $name => $value) {
            if ($value == ($defaults[$name] ?? 1.0)) {
                continue;
            }

            $parts[] = $name.': '.rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
        }

        return implode('; ', $parts);
    }

    /**
     * The longest side a photo or icon uploaded in this section is kept at, or null for the usual limit.
     * Menu photos are shown small, so 1200px is plenty; icons need only a few hundred.
     */
    public static function uploadLimit(?string $section, string $field): ?int
    {
        if ($section === null || ! str_starts_with($section, 'dining_page_')) {
            return null;
        }

        if ($section === 'dining_page_food_menu_icons' || $field === 'icon_image') {
            return 256;
        }

        if (in_array($section, [self::DISH, 'dining_page_drinks_items'], true) && $field === 'image') {
            return 1200;
        }

        return null;
    }
}
