<?php

namespace App\Support;

use App\Models\HomeContent;
use App\Models\SeasonalRate;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Everything the booking page needs to know about each villa: photos, description,
 * the regular and promo nightly rate, the promo dates and the Terms & Conditions.
 * Rates and terms are edited in the admin (Site content > Booking); config/booking.php
 * supplies the starting values. The price of a stay is always worked out here, on the
 * server, never trusted from the browser.
 */
class BookingRates
{
    /**
     * @return array<string, array<string, mixed>> keyed by villa slug
     */
    public static function villas(): array
    {
        $rows = HomeContent::where('active', true)->orderBy('sort_order')->get()->groupBy('section');
        $rates = $rows->get('booking_rates', collect());
        $out = [];

        // A villa whose rates entry is switched OFF in the admin is not bookable online.
        $off = HomeContent::where('section', 'booking_rates')->where('active', false)->pluck('title')
            ->map(fn ($t) => Str::slug((string) $t))->all();

        $num = fn ($v) => ($n = (float) preg_replace('/[^\d.]/', '', (string) $v)) > 0 ? $n : null;

        $seasons = static::seasons();

        foreach ($rows->get('villas', collect()) as $villa) {
            $slug = Str::slug($villa->title);

            if (in_array($slug, $off, true)) {
                continue;
            }

            $row = $rates->first(fn ($r) => Str::slug((string) $r->title) === $slug);
            $cfg = config("booking.rates.{$slug}", []);

            $regular = $num($row?->subtitle) ?? $num($cfg['regular'] ?? null) ?? $num($villa->price) ?? 0.0;
            $promo = $row ? $num($row->price) : $num($cfg['promo'] ?? null);
            $from = static::date($row ? $row->button_text : ($cfg['from'] ?? null));
            $to = static::date($row ? $row->button_url : ($cfg['to'] ?? null));

            if (! $promo || ! $from || ! $to || $to->lt($from)) {
                $promo = $from = $to = null;
            }

            $terms = trim((string) ($row?->description ?: config('booking.terms')));
            $terms = str_replace(['\\r\\n', '\\n'], "\n", $terms); // text pasted with literal \n

            $photos = collect(array_merge((array) $villa->gallery, [$villa->image]))
                ->filter(fn ($p) => is_string($p) && filled($p) && file_exists(public_path($p)))
                ->unique()->values()->all();

            $out[$slug] = [
                'slug' => $slug,
                'title' => $villa->title,
                'description' => $villa->description ?: config("villas.{$slug}.intro", ''),
                'photos' => $photos,
                'regular' => $regular,
                'promo' => $promo,
                'from' => $from,
                'to' => $to,
                'seasons' => static::seasonsFor($seasons, $slug),
                'terms' => $terms,
            ];
        }

        return $out;
    }

    /**
     * Rate for one night. The promo wins on the nights it covers; otherwise a seasonal rate
     * (Admin > Availability) applies; otherwise the regular rate. Dates are inclusive.
     */
    public static function rate(array $villa, Carbon $night): float
    {
        if ($villa['promo'] && $night->gte($villa['from']) && $night->lte($villa['to'])) {
            return (float) $villa['promo'];
        }

        return static::baseRate($villa, $night);
    }

    /** The nightly price without the promo: the seasonal rate for that night if there is one, else the regular rate. */
    public static function baseRate(array $villa, Carbon $night): float
    {
        $price = null;

        // Ordered weakest to strongest (see seasonsFor), so the last match wins.
        foreach ((array) ($villa['seasons'] ?? []) as $season) {
            if ($night->gte($season['from']) && $night->lte($season['to'])) {
                $price = (float) $season['price'];
            }
        }

        return $price ?? (float) $villa['regular'];
    }

    /**
     * All seasonal rates, as plain arrays.
     *
     * @return array<int, array{slug:?string,name:string,from:Carbon,to:Carbon,price:float}>
     */
    protected static function seasons(): array
    {
        try {
            return SeasonalRate::orderBy('start_date')->orderBy('id')->get()
                ->map(fn (SeasonalRate $r) => [
                    'slug' => $r->villa_slug ?: null,
                    'name' => (string) $r->name,
                    'from' => $r->start_date->copy()->startOfDay(),
                    'to' => $r->end_date->copy()->startOfDay(),
                    'price' => (float) $r->price,
                ])
                ->all();
        } catch (\Throwable) {
            return []; // table not migrated yet
        }
    }

    /**
     * The seasonal rates that apply to one villa, weakest first: rates for every villa come before
     * rates for this villa alone, and among those the one that starts later comes last (so the more
     * specific, later season wins where two overlap).
     *
     * @param  array<int, array<string, mixed>>  $seasons
     * @return array<int, array<string, mixed>>
     */
    protected static function seasonsFor(array $seasons, string $slug): array
    {
        $mine = array_values(array_filter($seasons, fn (array $s) => $s['slug'] === null || $s['slug'] === $slug));

        usort($mine, fn (array $a, array $b) => [$a['slug'] === null ? 0 : 1, $a['from']->timestamp]
            <=> [$b['slug'] === null ? 0 : 1, $b['from']->timestamp]);

        return $mine;
    }

    /** @return array{nights:int,total:float,regular_total:float} */
    public static function quote(array $villa, Carbon $checkIn, Carbon $checkOut): array
    {
        $nights = 0;
        $total = 0.0;
        $regular = 0.0;

        for ($d = $checkIn->copy()->startOfDay(); $d->lt($checkOut->copy()->startOfDay()); $d->addDay()) {
            $nights++;
            $total += static::rate($villa, $d);
            $regular += static::baseRate($villa, $d);
        }

        return ['nights' => $nights, 'total' => $total, 'regular_total' => $regular];
    }

    /** True while the promo is running or still to come (so its badge and struck-through price are shown). */
    public static function promoVisible(array $villa): bool
    {
        return $villa['promo'] && $villa['to']->gte(now()->startOfDay());
    }

    protected static function date($value): ?Carbon
    {
        try {
            return filled($value) ? Carbon::parse($value)->startOfDay() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
