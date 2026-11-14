<?php

use App\Models\HomeContent;
use Database\Seeders\YogaContentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * The redesigned Yoga Reset page (page layout, notice card, programme with day tabs, open classes,
 * resort, gallery, teacher, package and questions) loads its starting content into the admin.
 * Anything already edited is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(YogaContentSeeder::class)->run();

        // The old "Your pace" entry is replaced by "Open classes: text". Remove it only while it is
        // still the starting content, so nothing a person wrote is lost.
        HomeContent::where('section', 'yoga_personal')
            ->where('title', config('yoga.personal.title'))
            ->where('description', config('yoga.personal.text'))
            ->delete();
    }

    public function down(): void
    {
        // Intentionally empty: removing content is not something to undo silently.
    }
};
