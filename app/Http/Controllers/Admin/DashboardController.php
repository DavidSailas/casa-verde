<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\HomeContent;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** How many days back a review still counts as "new" on the dashboard. */
    private const NEW_REVIEW_DAYS = 7;

    public function index(): View
    {
        $groups = HomeContent::pageGroups();
        $user = auth()->user();

        return view('admin.dashboard', [
            'groups' => $groups,
            'totalEntries' => $groups->sum('total'),
            'totalHidden' => $groups->sum('hidden_total'),
            'recent' => HomeContent::query()->latest('updated_at')->limit(6)->get(),
            'bookings' => $user->canAccess('bookings') ? $this->bookingsToday() : null,
            'reviews' => $user->canAccess('reviews') ? $this->newReviews() : null,
            'newReviewDays' => self::NEW_REVIEW_DAYS,
        ]);
    }

    /**
     * Requests that came in today, guests arriving today, and how many still wait for an answer.
     * The dashboard never breaks when the table is missing (a fresh install that has not migrated).
     *
     * @return array{received: Collection<int, Booking>, arriving: Collection<int, Booking>, pending: int}
     */
    private function bookingsToday(): array
    {
        try {
            return [
                'received' => Booking::query()->whereDate('created_at', today())->latest()->limit(10)->get(),
                'arriving' => Booking::query()->whereDate('check_in', today())->where('status', '!=', 'cancelled')->orderBy('name')->limit(10)->get(),
                'pending' => Booking::pendingCount(),
            ];
        } catch (\Throwable) {
            return ['received' => collect(), 'arriving' => collect(), 'pending' => 0];
        }
    }

    /**
     * @return array{latest: Collection<int, Review>, total: int, waiting: int}
     */
    private function newReviews(): array
    {
        try {
            $new = Review::query()->where('created_at', '>=', now()->subDays(self::NEW_REVIEW_DAYS));

            return [
                'latest' => (clone $new)->latest()->limit(5)->get(),
                'total' => (clone $new)->count(),
                'waiting' => Review::waitingForApproval(),
            ];
        } catch (\Throwable) {
            return ['latest' => collect(), 'total' => 0, 'waiting' => 0];
        }
    }
}
