<?php

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardBooking(array $overrides = []): Booking
{
    return Booking::create($overrides + [
        'reference' => 'CV-DASH'.random_int(1000, 9999),
        'villa_slug' => 'standard-villa', 'villa_title' => 'Standard Villa',
        'check_in' => now()->addDays(10)->startOfDay(), 'check_out' => now()->addDays(12)->startOfDay(),
        'nights' => 2, 'adults' => 2, 'children' => 0,
        'name' => 'Some Guest', 'email' => 'guest@example.com', 'phone' => '+63 900 000 0000', 'country' => 'Philippines',
        'total' => 18000, 'status' => 'pending', 'terms_accepted_at' => now(),
    ]);
}

test('the dashboard lists the booking requests received today', function () {
    dashboardBooking(['name' => 'Maria Today']);
    $old = dashboardBooking(['name' => 'Pedro Lastmonth']);
    $old->forceFill(['created_at' => now()->subDays(30)])->save();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Bookings today')
        ->assertSee('Maria Today')
        ->assertDontSee('Pedro Lastmonth');
});

test('the dashboard lists guests arriving today but not cancelled ones', function () {
    dashboardBooking(['name' => 'Arriving Anna', 'check_in' => today(), 'check_out' => today()->addDays(2), 'status' => 'confirmed'])
        ->forceFill(['created_at' => now()->subDays(20)])->save();
    dashboardBooking(['name' => 'Cancelled Carl', 'check_in' => today(), 'check_out' => today()->addDays(2), 'status' => 'cancelled'])
        ->forceFill(['created_at' => now()->subDays(20)])->save();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Arriving Anna')
        ->assertDontSee('Cancelled Carl');
});

test('the dashboard shows reviews from the last week and leaves older ones out', function () {
    Review::create(['name' => 'Fresh Reviewer', 'rating' => 5, 'comment' => 'Wonderful stay.']);
    $old = Review::create(['name' => 'Ancient Reviewer', 'rating' => 4, 'comment' => 'Nice long ago.']);
    $old->forceFill(['created_at' => now()->subDays(40)])->save();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('New reviews')
        ->assertSee('Fresh Reviewer')
        ->assertDontSee('Ancient Reviewer');
});

test('an editor sees neither guest bookings nor reviews on the dashboard', function () {
    dashboardBooking(['name' => 'Private Guest']);
    Review::create(['name' => 'Private Reviewer', 'rating' => 5, 'comment' => 'Hidden from editors.']);

    $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'editor']))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Bookings today')
        ->assertDontSee('Private Guest')
        ->assertDontSee('New reviews')
        ->assertDontSee('Private Reviewer');
});
