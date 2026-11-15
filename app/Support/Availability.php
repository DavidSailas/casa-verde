<?php

namespace App\Support;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\VillaUnit;
use Carbon\Carbon;

/**
 * Which nights a villa can still be booked.
 *
 * A night is taken when the resort has closed it by hand (Admin > Availability), or when
 * confirmed bookings already use every villa of that type. Requests that are still pending
 * do not hold dates (so nobody can lock the calendar with a request); the admin cannot
 * confirm a booking that would double-book a night (see conflict()).
 *
 * A stay is a list of nights: the check-in night up to, but not including, the check-out day.
 * A guest may therefore check in on the day another guest checks out.
 */
class Availability
{
    /** How many villas of this type exist (one unless the admin says otherwise). */
    public static function units(string $slug): int
    {
        try {
            return max(1, (int) (VillaUnit::where('villa_slug', $slug)->value('units') ?? 1));
        } catch (\Throwable) {
            return 1; // table not migrated yet
        }
    }

    /**
     * Nights in [from, to) that cannot be booked, as 'Y-m-d' => reason ('closed' or 'full').
     *
     * @return array<string, string>
     */
    public static function takenNights(string $slug, Carbon $from, Carbon $to, ?int $ignoreBookingId = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();

        if ($to->lte($from)) {
            return [];
        }

        $taken = [];

        try {
            $lastNight = $to->copy()->subDay();

            $blocks = BlockedDate::query()
                ->where(fn ($q) => $q->whereNull('villa_slug')->orWhere('villa_slug', $slug))
                ->whereDate('start_date', '<=', $lastNight->toDateString())
                ->whereDate('end_date', '>=', $from->toDateString())
                ->get();

            foreach ($blocks as $block) {
                $start = $block->start_date->copy()->startOfDay()->max($from);
                $end = $block->end_date->copy()->startOfDay()->min($lastNight);

                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $taken[$d->toDateString()] = 'closed';
                }
            }
        } catch (\Throwable) {
            return $taken; // tables not migrated yet: nothing is taken
        }

        $units = static::units($slug);

        foreach (static::bookedCounts($slug, $from, $to, $ignoreBookingId) as $night => $count) {
            if ($count >= $units && ! isset($taken[$night])) {
                $taken[$night] = 'full';
            }
        }

        ksort($taken);

        return $taken;
    }

    /**
     * How many confirmed bookings use each night in [from, to).
     *
     * @return array<string, int> 'Y-m-d' => number of bookings
     */
    public static function bookedCounts(string $slug, Carbon $from, Carbon $to, ?int $ignoreBookingId = null): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();
        $used = [];

        try {
            $bookings = Booking::query()
                ->where('villa_slug', $slug)
                ->where('status', 'confirmed')
                ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
                ->whereDate('check_in', '<', $to->toDateString())
                ->whereDate('check_out', '>', $from->toDateString())
                ->get(['check_in', 'check_out']);
        } catch (\Throwable) {
            return $used;
        }

        foreach ($bookings as $booking) {
            $start = $booking->check_in->copy()->startOfDay()->max($from);
            $end = $booking->check_out->copy()->startOfDay()->min($to);

            for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
                $key = $d->toDateString();
                $used[$key] = ($used[$key] ?? 0) + 1;
            }
        }

        return $used;
    }

    /** The first night of the stay that cannot be booked ('Y-m-d'), or null when the whole stay is free. */
    public static function conflict(string $slug, Carbon $checkIn, Carbon $checkOut, ?int $ignoreBookingId = null): ?string
    {
        return array_key_first(static::takenNights($slug, $checkIn, $checkOut, $ignoreBookingId));
    }

    /**
     * Every night that cannot be booked from today for the coming months, for the booking page.
     *
     * @return array<int, string>
     */
    public static function unavailableDates(string $slug, int $months = 18): array
    {
        $from = now()->startOfDay();

        return array_keys(static::takenNights($slug, $from, $from->copy()->addMonths($months)));
    }
}
