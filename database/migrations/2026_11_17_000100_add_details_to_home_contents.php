<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each villa entry can carry its own detail-page content (intro, story, highlights, amenities,
 * floor area, hero photo, floor plan), edited on the villa entry in the admin. A new villa or
 * package (for example a Family Bundle) therefore needs no code or config change.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('home_contents', 'details')) {
            Schema::table('home_contents', function (Blueprint $table) {
                $table->text('details')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('home_contents', 'details')) {
            Schema::table('home_contents', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }
    }
};
