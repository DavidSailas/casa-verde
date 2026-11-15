<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Availability: dates the resort closes by hand, seasonal nightly rates, and how many
 * villas of each type exist. Managed in the admin under Availability.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Nights nobody can book: maintenance, owner use, a private event. villa_slug null = every villa.
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->string('villa_slug')->nullable();
            $table->date('start_date');
            $table->date('end_date'); // the last blocked night, inclusive
            $table->string('reason', 120)->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });

        // A nightly price for a period (high season, Christmas...). villa_slug null = every villa.
        Schema::create('seasonal_rates', function (Blueprint $table) {
            $table->id();
            $table->string('villa_slug')->nullable();
            $table->string('name', 80);
            $table->date('start_date');
            $table->date('end_date'); // the last night this price applies to, inclusive
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });

        // How many villas of a type exist. A type that is not listed here counts as one.
        Schema::create('villa_units', function (Blueprint $table) {
            $table->id();
            $table->string('villa_slug')->unique();
            $table->unsignedSmallInteger('units')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_units');
        Schema::dropIfExists('seasonal_rates');
        Schema::dropIfExists('blocked_dates');
    }
};
