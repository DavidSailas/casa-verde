<?php

namespace App\Support;

use App\Models\HomeContent;
use Illuminate\Support\Str;

/**
 * The content of one villa's detail page (/villas/{slug}).
 *
 * Standard Villa and Premium Villa keep their starting content in config/villas.php. Anything
 * saved on the villa entry in the admin ("Villa detail page" box) wins over it, and a villa
 * that is new (a Family Bundle, a Honeymoon Suite...) works from the admin alone.
 */
class VillaDetails
{
    /**
     * @return array<string, mixed>  same shape as config("villas.{slug}"), plus "custom" (the keys set in the admin)
     */
    public static function for(HomeContent $villa): array
    {
        $slug = Str::slug((string) $villa->title);
        $detail = $slug !== '' ? (array) config("villas.{$slug}", []) : [];
        $own = is_array($villa->details) ? $villa->details : [];
        $custom = [];

        if (filled($intro = trim((string) ($own['intro'] ?? '')))) {
            $detail['intro'] = $intro;
            $custom[] = 'intro';
        }

        if (($heading = static::lines($own['story_title'] ?? '')) !== []) {
            $detail['story_title'] = $heading;
            $custom[] = 'story_title';
        }

        if (($story = static::paragraphs($own['story_text'] ?? '')) !== []) {
            $detail['story_text'] = $story;
            $custom[] = 'story_text';
        }

        if (($highlights = static::highlights($own['highlights'] ?? [])) !== []) {
            $detail['highlights'] = $highlights;
            $custom[] = 'highlights';
        }

        $columns = collect((array) ($own['amenities'] ?? []))
            ->map(fn ($text) => static::lines($text))
            ->filter()
            ->values()
            ->all();

        if ($columns !== []) {
            $detail['amenities'] = $columns;
            $custom[] = 'amenities';
        }

        // Sleeping arrangement: one row per bedroom (room name + beds), set on the villa entry in the admin.
        $bedrooms = static::bedrooms($own['bedrooms'] ?? []);

        if ($bedrooms !== []) {
            $detail['bedrooms'] = $bedrooms;
            $custom[] = 'bedrooms';
        }

        // Quick facts for the card and the detail page. The bedroom count follows the list above, so it never
        // disagrees with it; guests and bathrooms are typed in the admin. Anything left empty is simply not shown.
        $guests = static::whole($own['capacity']['guests'] ?? null);
        $bathrooms = static::whole($own['capacity']['bathrooms'] ?? null);

        if ($bedrooms !== [] || $guests !== null || $bathrooms !== null) {
            $detail['capacity'] = array_filter([
                'guests' => $guests,
                'bedrooms' => $bedrooms !== [] ? count($bedrooms) : null,
                'bathrooms' => $bathrooms,
            ], fn ($value) => $value !== null);
            $custom[] = 'capacity';
        }

        $interior = static::number($own['layout']['interior'] ?? null);
        $terrace = static::number($own['layout']['terrace'] ?? null);
        $plan = static::image($own['plan'] ?? null);

        if ($interior !== null || $terrace !== null) {
            $detail['layout'] = [
                'plan' => $plan ?? ($detail['layout']['plan'] ?? null),
                'interior' => $interior ?? 0,
                'terrace' => $terrace ?? 0,
                'total' => ($interior ?? 0) + ($terrace ?? 0),
            ];
            $custom[] = 'layout';
        } elseif ($plan !== null && isset($detail['layout'])) {
            $detail['layout']['plan'] = $plan;
        }

        if (($hero = static::image($own['hero'] ?? null)) !== null) {
            $detail['hero'] = $hero;
            $custom[] = 'hero';
        }

        // Photos beside the story: the villa's own gallery photos when nothing else is set.
        if (empty($detail['gallery'])) {
            $detail['gallery'] = collect((array) $villa->gallery)
                ->filter(fn ($path) => static::image($path) !== null)
                ->take(3)
                ->values()
                ->all();
        }

        $detail['custom'] = $custom;

        return $detail;
    }

    /** One non-empty line per item, from a textarea or an array. */
    public static function lines(mixed $value): array
    {
        $value = is_array($value) ? implode("\n", $value) : (string) $value;

        return collect(preg_split('/\R/', $value) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /** Paragraphs separated by a blank line. */
    public static function paragraphs(mixed $value): array
    {
        $value = is_array($value) ? implode("\n\n", $value) : (string) $value;

        return collect(preg_split('/\R\s*\R/', trim($value)) ?: [])
            ->map(fn ($paragraph) => trim(preg_replace('/\s*\R\s*/', ' ', $paragraph)))
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, array{icon:string, label:string, icon2?:string}> */
    protected static function highlights(mixed $rows): array
    {
        return collect((array) $rows)
            ->filter(fn ($row) => is_array($row) && filled(trim((string) ($row['label'] ?? ''))))
            ->map(fn (array $row) => array_filter([
                'icon' => trim((string) ($row['icon'] ?? '')) ?: 'lotus',
                // Optional second icon shown beside the first (for example a queen bed and twin beds).
                'icon2' => trim((string) ($row['icon2'] ?? '')),
                'label' => trim((string) $row['label']),
            ], fn ($value) => $value !== ''))
            ->values()
            ->all();
    }

    /** @return array<int, array{name:string, beds:string}> */
    protected static function bedrooms(mixed $rows): array
    {
        return collect((array) $rows)
            ->filter(fn ($row) => is_array($row) && (filled(trim((string) ($row['name'] ?? ''))) || filled(trim((string) ($row['beds'] ?? '')))))
            ->map(fn (array $row) => [
                'name' => trim((string) ($row['name'] ?? '')),
                'beds' => trim((string) ($row['beds'] ?? '')),
            ])
            ->take(8)
            ->values()
            ->map(fn (array $row, int $index) => $row['name'] !== '' ? $row : ['name' => 'Bedroom '.($index + 1)] + $row)
            ->all();
    }

    /** A whole number of one or more (guests, bathrooms), else null. */
    protected static function whole(mixed $value): ?int
    {
        $number = (int) preg_replace('/\D/', '', (string) $value);

        return $number > 0 ? min($number, 99) : null;
    }

    protected static function number(mixed $value): ?float
    {
        $number = preg_replace('/[^\d.]/', '', (string) $value);

        return $number !== '' && (float) $number > 0 ? (float) $number : null;
    }

    /** A picture path under /public that exists, else null. */
    protected static function image(mixed $path): ?string
    {
        $path = trim((string) $path);

        return $path !== '' && ! str_contains($path, '..') && file_exists(public_path($path)) ? $path : null;
    }
}
