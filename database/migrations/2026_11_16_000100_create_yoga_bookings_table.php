<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yoga_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('type');                       // retreat | class | private
            $table->string('option_title');
            $table->string('session')->nullable();        // the class chosen (open classes)
            $table->date('start_date');
            $table->date('end_date')->nullable();         // retreat only
            $table->unsignedSmallInteger('nights')->nullable();
            $table->string('time_pref')->nullable();      // private sessions
            $table->unsignedSmallInteger('participants')->default(1);
            $table->string('level')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yoga_bookings');
    }
};
