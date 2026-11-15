<?php

namespace App\Support;

use App\Models\HomeContent;
use Illuminate\Support\Str;

/**
 * What goes in the <head> of every public page for search engines and link previews:
 * the tab title, the description, the share picture and the Google Analytics ID.
 *
 * It is edited in the admin (Site-wide > Search and sharing): one entry per page, found by
 * the page name, plus a "Whole site" entry that every page falls back to. config/pagecontent.php
 * only supplies the starting values. A page that has no entry, or whose entry is switched off,
 * keeps the wording its view passes in.
 */
class SiteSeo
{
    /**
     * The active entries, keyed by the slug of the page name ("whole-site", "home", "contact-us"...).
     *
     * @return array<string, HomeContent>
     */
    public static function rows(): array
    {
        static $cache = null;

        if ($cache !== null && ! app()->runningUnitTests()) {
            return $cache;
        }

        $saved = collect();

        try {
            $saved = HomeContent::where('section', 'site_seo')->where('active', true)->orderBy('sort_order')->get();
        } catch (\Throwable) {
            // table not ready yet: use the starting values
        }

        if ($saved->isEmpty()) {
            $saved = collect(config('pagecontent.site_seo', []))
                ->map(fn (array $attributes) => new HomeContent($attributes + ['active' => true]));
        }

        $rows = [];

        foreach ($saved as $row) {
            $rows[Str::slug((string) $row->title)] = $row;
        }

        return $cache = $rows;
    }

    /**
     * @param  string  $page  slug of the page name in the admin ("contact-us"), or '' for pages that have no entry
     * @param  string  $fallbackTitle  tab title used when the admin leaves it empty
     * @param  string  $fallbackDescription  description used when the admin leaves it empty
     * @param  string|null  $fallbackImage  picture path used when the page entry has none
     * @return array{title:string,site:string,description:string,image:?string,canonical:string,analytics:string}
     */
    public static function tags(string $page, string $fallbackTitle = '', string $fallbackDescription = '', ?string $fallbackImage = null): array
    {
        $rows = static::rows();
        $site = $rows['whole-site'] ?? null;
        $row = $page !== '' ? ($rows[$page] ?? null) : null;

        $suffix = trim((string) ($site?->subtitle ?? '')) ?: 'Casa Verde Cliff Resort & Spa';
        $tab = trim((string) ($row?->subtitle ?? '')) ?: trim($fallbackTitle);

        $description = trim((string) ($row?->description ?? ''))
            ?: trim($fallbackDescription)
            ?: trim((string) ($site?->description ?? ''));

        return [
            'title' => ($tab === '' || strcasecmp($tab, $suffix) === 0) ? $suffix : $tab.' | '.$suffix,
            'site' => $suffix,
            'description' => Str::limit(trim(preg_replace('/\s+/', ' ', $description)), 300, ''),
            'image' => static::image($row?->image) ?? static::image($fallbackImage) ?? static::image($site?->image),
            'canonical' => url()->current(),
            'analytics' => static::analyticsId((string) ($site?->button_text ?? '')),
        ];
    }

    /** A Google Analytics / tag ID such as G-ABC123XYZ9, else '' (so a typo can never break a page). */
    public static function analyticsId(string $value): string
    {
        $value = strtoupper(trim($value));

        return preg_match('/^(G|GT|AW)-[A-Z0-9]{4,20}$/', $value) === 1 ? $value : '';
    }

    /** The full address of a picture under /public that exists, else null. */
    protected static function image(mixed $path): ?string
    {
        $path = ltrim(trim((string) $path), '/');

        return $path !== '' && ! str_contains($path, '..') && file_exists(public_path($path)) ? asset($path) : null;
    }
}
