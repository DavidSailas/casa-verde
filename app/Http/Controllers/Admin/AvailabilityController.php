<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\SeasonalRate;
use App\Models\VillaUnit;
use App\Support\Availability;
use App\Support\BookingRates;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /** The month calendar for every villa, the closed dates, the seasonal rates and the number of villas per type. */
    public function index(Request $request): View
    {
        $villas = $this->villas();
        $month = $this->month($request);
        $end = $month->copy()->addMonth();

        $calendar = [];

        foreach ($villas as $slug => $title) {
            $closed = Availability::takenNights($slug, $month, $end);
            $booked = Availability::bookedCounts($slug, $month, $end);
            $units = Availability::units($slug);
            $days = [];

            for ($d = $month->copy(); $d->lt($end); $d->addDay()) {
                $key = $d->toDateString();
                $count = $booked[$key] ?? 0;

                $days[$d->day] = [
                    'state' => ($closed[$key] ?? null) === 'closed' ? 'closed' : ($count >= $units ? 'full' : ($count > 0 ? 'partly' : 'free')),
                    'booked' => $count,
                ];
            }

            $calendar[$slug] = ['title' => $title, 'units' => $units, 'days' => $days];
        }

        return view('admin.availability.index', [
            'villas' => $villas,
            'month' => $month,
            'previous' => $month->copy()->subMonth()->format('Y-m'),
            'next' => $month->copy()->addMonth()->format('Y-m'),
            'daysInMonth' => $month->daysInMonth,
            'calendar' => $calendar,
            'blocks' => BlockedDate::whereDate('end_date', '>=', now()->toDateString())->orderBy('start_date')->get(),
            'seasons' => SeasonalRate::whereDate('end_date', '>=', now()->toDateString())->orderBy('start_date')->get(),
        ]);
    }

    public function storeBlock(Request $request): RedirectResponse
    {
        $data = $this->blockData($request);
        $block = BlockedDate::create($data);

        return back()->with('status', 'The dates were closed.'.$this->bookingsNote($block));
    }

    public function updateBlock(Request $request, BlockedDate $blockedDate): RedirectResponse
    {
        $blockedDate->update($this->blockData($request));

        return back()->with('status', 'The closed dates were updated.'.$this->bookingsNote($blockedDate));
    }

    public function destroyBlock(BlockedDate $blockedDate): RedirectResponse
    {
        $blockedDate->delete();

        return back()->with('status', 'The dates are open for booking again.');
    }

    public function storeSeason(Request $request): RedirectResponse
    {
        SeasonalRate::create($this->seasonData($request));

        return back()->with('status', 'The seasonal rate was added.');
    }

    public function updateSeason(Request $request, SeasonalRate $seasonalRate): RedirectResponse
    {
        $seasonalRate->update($this->seasonData($request));

        return back()->with('status', 'The seasonal rate was updated.');
    }

    public function destroySeason(SeasonalRate $seasonalRate): RedirectResponse
    {
        $seasonalRate->delete();

        return back()->with('status', 'The seasonal rate was deleted.');
    }

    /** How many villas of each type exist. */
    public function updateUnits(Request $request): RedirectResponse
    {
        $request->validate([
            'units' => ['required', 'array'],
            'units.*' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        foreach ($this->villas() as $slug => $title) {
            if ($request->has("units.{$slug}")) {
                VillaUnit::updateOrCreate(['villa_slug' => $slug], ['units' => (int) $request->input("units.{$slug}")]);
            }
        }

        return back()->with('status', 'The number of villas was saved.');
    }

    /** @return array<string, string> bookable villas, slug => name */
    protected function villas(): array
    {
        return collect(BookingRates::villas())->map(fn (array $villa) => $villa['title'])->all();
    }

    protected function month(Request $request): Carbon
    {
        $value = (string) $request->query('month', '');

        try {
            return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1
                ? Carbon::createFromFormat('!Y-m', $value)
                : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    /** @return array{villa_slug:?string,start_date:string,end_date:string,reason:?string} */
    protected function blockData(Request $request): array
    {
        $data = $request->validate([
            'villa_slug' => ['nullable', Rule::in(array_keys($this->villas()))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:120'],
        ], [
            'end_date.after_or_equal' => 'The last closed night cannot be before the first one.',
        ]);

        $data['villa_slug'] = filled($data['villa_slug'] ?? null) ? $data['villa_slug'] : null;

        return $data;
    }

    /** @return array{villa_slug:?string,name:string,start_date:string,end_date:string,price:float} */
    protected function seasonData(Request $request): array
    {
        $data = $request->validate([
            'villa_slug' => ['nullable', Rule::in(array_keys($this->villas()))],
            'name' => ['required', 'string', 'max:80'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:1', 'max:10000000'],
        ], [
            'end_date.after_or_equal' => 'The last night of the season cannot be before the first one.',
        ]);

        $data['villa_slug'] = filled($data['villa_slug'] ?? null) ? $data['villa_slug'] : null;

        return $data;
    }

    /** A reminder when dates are closed over bookings that are already confirmed. */
    protected function bookingsNote(BlockedDate $block): string
    {
        $count = Booking::where('status', 'confirmed')
            ->when($block->villa_slug, fn ($q) => $q->where('villa_slug', $block->villa_slug))
            ->whereDate('check_in', '<=', $block->end_date->toDateString())
            ->whereDate('check_out', '>', $block->start_date->toDateString())
            ->count();

        return $count > 0
            ? " Note: {$count} confirmed ".($count === 1 ? 'booking is' : 'bookings are').' already inside these dates. Closing does not cancel them; check Bookings.'
            : '';
    }
}
