<?php

use Database\Seeders\SiteSeoSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Loads the "Search and sharing" entries (page titles, descriptions, share pictures and
 * the Google Analytics ID) into the admin. Existing entries are never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(SiteSeoSeeder::class)->run();
    }

    public function down(): void
    {
        // Intentionally empty: removing content is not something to undo silently.
    }
};
