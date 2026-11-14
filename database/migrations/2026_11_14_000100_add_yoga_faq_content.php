<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Superseded by 2026_11_15_000100_load_yoga_retreat_layout_content. It already ran once and loaded the
 * first version of the Yoga questions, so it is kept (empty) only so the migration history stays intact.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Nothing to do.
    }

    public function down(): void
    {
        // Nothing to do.
    }
};
