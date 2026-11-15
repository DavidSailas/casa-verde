<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use Illuminate\Database\Seeder;

class SiteSeoSeeder extends Seeder
{
    /**
     * Load the "Search and sharing" entries (one per page) into the admin without
     * touching anything else. Safe to run more than once: an entry that exists is kept.
     */
    public function run(): void
    {
        foreach (config('pagecontent.site_seo', []) as $index => $attributes) {
            HomeContent::firstOrCreate(
                ['section' => 'site_seo', 'sort_order' => $index + 1],
                $attributes + ['active' => true],
            );
        }
    }
}
