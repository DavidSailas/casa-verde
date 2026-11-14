<?php

use App\Models\HomeContent;
use Illuminate\Database\Migrations\Migration;

/**
 * Makes the header menu match the current design:
 * Villas, Experiences, Wellness, Dining, Long Stay, Discover (Reviews + Journal), Yoga Reset.
 * About and Contact are switched off in the header (they stay in the admin and the footer).
 * Safe to run more than once: rows that already exist are never duplicated.
 */
return new class extends Migration
{
    public function up(): void
    {
        $order = ['villas', 'experiences', 'wellness', 'dining', 'long stay', 'reviews', 'journal', 'yoga reset', 'about', 'contact'];
        $off = ['about', 'contact'];
        $missing = [
            'reviews' => ['REVIEWS', '/reviews'],
            'journal' => ['JOURNAL', '/blog'],
            'yoga reset' => ['YOGA RESET', '/yoga-reset'],
        ];

        foreach ($missing as $key => [$title, $url]) {
            $exists = HomeContent::where('section', 'navigation')
                ->whereRaw('lower(trim(title)) = ?', [$key])
                ->exists();

            if (! $exists) {
                HomeContent::create([
                    'section' => 'navigation',
                    'title' => $title,
                    'button_url' => $url,
                    'sort_order' => 99,
                    'active' => true,
                ]);
            }
        }

        foreach ($order as $i => $key) {
            HomeContent::where('section', 'navigation')
                ->whereRaw('lower(trim(title)) = ?', [$key])
                ->update(['sort_order' => $i + 1, 'active' => ! in_array($key, $off, true)]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: removing menu content is not something to undo silently.
    }
};
