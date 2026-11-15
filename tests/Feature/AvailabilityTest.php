<?php

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\HomeContent;
use App\Models\SeasonalRate;
use App\Models\User;
use App\Models\VillaUnit;
use App\Support\Availability;
use App\Support\BookingRates;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    HomeContent::whereIn('section', ['villas', 'booking_rates', 'booking_page', 'booking_labels', 'booking_confirmation', 'booking_settings'])->delete();

    HomeContent::create(['section' => 'villas', 'title' => 'Standard Villa', 'price' => 9000, 'description' => 'A roomy villa.', 'sort_order' => 1, 'active' => true]);

    // Regular 9,000 a night, no promo.
    HomeContent::create([
        'section' => 'booking_rates', 'title' => 'Standard Villa', 'subtitle' => '9000',
        'description' => 'Terms.', 'sort_order' => 1, 'active' => true,
    ]);
});

function stay(int $fromDays, int $toDays, array $overrides = []): array
{
    return array_merge([
        'check_in' => now()->addDays($fromDays)->toDateString(),
        'check_out' => now()->addDays($toDays)->toDateString(),
        'adults' => 2,
        'children' => 0,
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'phone' => '+63 917 000 0000',
        'country' => 'Philippines',
        'terms' => '1',
        '_villa' => 'standard-villa',
    ], $overrides);
}

function bookingFor(int $fromDays, int $toDays, string $status = 'confirmed'): Booking
{
    return Booking::create([
        'reference' => 'CV-TEST'.random_int(1000, 9999),
        'villa_slug' => 'standard-villa', 'villa_title' => 'Standard Villa',
        'check_in' => now()->addDays($fromDays)->startOfDay(), 'check_out' => now()->addDays($toDays)->startOfDay(),
        'nights' => $toDays - $fromDays, 'adults' => 2, 'children' => 0,
        'name' => 'Earlier Guest', 'email' => 'earlier@example.com', 'phone' => '+63 900 000 0000', 'country' => 'Philippines',
        'total' => 18000, 'status' => $status, 'terms_accepted_at' => now(),
    ]);
}

test('a villa with no bookings or closed dates is free', function () {
    expect(Availability::conflict('standard-villa', now()->addDays(5), now()->addDays(8)))->toBeNull();
});

test('closed dates cannot be booked, but the night after them can', function () {
    BlockedDate::create(['villa_slug' => 'standard-villa', 'start_date' => now()->addDays(10)->toDateString(), 'end_date' => now()->addDays(12)->toDateString(), 'reason' => 'Repairs']);

    Mail::fake();

    $this->post('/book/standard-villa', stay(11, 14))
        ->assertSessionHasErrors('check_in');

    expect(Booking::count())->toBe(0);

    // Checking in the morning after the last closed night is fine.
    $this->post('/book/standard-villa', stay(13, 15))
        ->assertRedirect(route('booking.confirmed'));
});

test('dates closed for all villas apply to every villa', function () {
    BlockedDate::create(['villa_slug' => null, 'start_date' => now()->addDays(4)->toDateString(), 'end_date' => now()->addDays(4)->toDateString()]);

    expect(Availability::conflict('standard-villa', now()->addDays(3), now()->addDays(6)))->toBe(now()->addDays(4)->toDateString());
});

test('a confirmed booking takes the nights it covers but not its check-out day', function () {
    bookingFor(20, 23);

    expect(Availability::conflict('standard-villa', now()->addDays(21), now()->addDays(24)))->toBe(now()->addDays(21)->toDateString())
        // the next guest may arrive on the day the first one leaves
        ->and(Availability::conflict('standard-villa', now()->addDays(23), now()->addDays(25)))->toBeNull()
        // and may leave on the day the first one arrives
        ->and(Availability::conflict('standard-villa', now()->addDays(18), now()->addDays(20)))->toBeNull();
});

test('a pending request does not hold the dates', function () {
    bookingFor(20, 23, 'pending');

    expect(Availability::conflict('standard-villa', now()->addDays(20), now()->addDays(23)))->toBeNull();
});

test('a cancelled booking frees its dates', function () {
    bookingFor(20, 23, 'cancelled');

    expect(Availability::conflict('standard-villa', now()->addDays(20), now()->addDays(23)))->toBeNull();
});

test('two villas of one type can be booked for the same night, a third cannot', function () {
    VillaUnit::create(['villa_slug' => 'standard-villa', 'units' => 2]);

    bookingFor(20, 23);
    expect(Availability::conflict('standard-villa', now()->addDays(20), now()->addDays(23)))->toBeNull();

    bookingFor(21, 24);
    expect(Availability::conflict('standard-villa', now()->addDays(20), now()->addDays(23)))->toBe(now()->addDays(21)->toDateString());
});

test('the guest form refuses nights that are already booked', function () {
    bookingFor(30, 33);

    Mail::fake();

    $this->post('/book/standard-villa', stay(32, 35))->assertSessionHasErrors('check_in');

    expect(Booking::where('status', 'pending')->count())->toBe(0);
});

test('the admin cannot confirm a booking that would double-book a night', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    bookingFor(40, 43);
    $second = bookingFor(41, 44, 'pending');

    $this->actingAs($admin)
        ->from(route('admin.bookings.index'))
        ->put(route('admin.bookings.update', $second), ['status' => 'confirmed'])
        ->assertSessionHasErrors('status');

    expect($second->fresh()->status)->toBe('pending');
});

test('the admin can confirm a booking when the dates are free', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Mail::fake();

    $booking = bookingFor(40, 43, 'pending');

    $this->actingAs($admin)
        ->put(route('admin.bookings.update', $booking), ['status' => 'confirmed'])
        ->assertSessionHasNoErrors();

    expect($booking->fresh()->status)->toBe('confirmed');
});

test('a seasonal rate sets the price per night on its nights', function () {
    SeasonalRate::create([
        'villa_slug' => 'standard-villa', 'name' => 'High season',
        'start_date' => now()->addDays(10)->toDateString(), 'end_date' => now()->addDays(11)->toDateString(), 'price' => 12000,
    ]);

    Mail::fake();

    // 3 nights: two at the 12,000 season price and one at the 9,000 regular price.
    $this->post('/book/standard-villa', stay(10, 13))->assertRedirect(route('booking.confirmed'));

    expect((float) Booking::firstOrFail()->total)->toBe(33000.0);
});

test('a seasonal rate for one villa beats one for all villas', function () {
    SeasonalRate::create(['villa_slug' => null, 'name' => 'All', 'start_date' => now()->addDays(10)->toDateString(), 'end_date' => now()->addDays(20)->toDateString(), 'price' => 10000]);
    SeasonalRate::create(['villa_slug' => 'standard-villa', 'name' => 'Just this villa', 'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(20)->toDateString(), 'price' => 15000]);

    $villa = BookingRates::villas()['standard-villa'];

    expect(BookingRates::rate($villa, Carbon::parse(now()->addDays(12))->startOfDay()))->toBe(15000.0)
        ->and(BookingRates::rate($villa, Carbon::parse(now()->addDays(30))->startOfDay()))->toBe(9000.0);
});

test('the booking page tells the browser which nights are taken', function () {
    BlockedDate::create(['villa_slug' => 'standard-villa', 'start_date' => now()->addDays(10)->toDateString(), 'end_date' => now()->addDays(10)->toDateString()]);

    $this->get('/book')
        ->assertOk()
        ->assertSee(now()->addDays(10)->toDateString());
});

test('the admin can open the calendar, close dates, add a seasonal rate and set the number of villas', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get(route('admin.availability.index'))
        ->assertOk()
        ->assertSee('Availability')
        ->assertSee('Standard Villa');

    $this->actingAs($admin)->post(route('admin.availability.blocks.store'), [
        'villa_slug' => 'standard-villa',
        'start_date' => now()->addDays(50)->toDateString(),
        'end_date' => now()->addDays(52)->toDateString(),
        'reason' => 'Roof repairs',
    ])->assertSessionHasNoErrors();

    expect(BlockedDate::count())->toBe(1);

    $this->actingAs($admin)->post(route('admin.availability.seasons.store'), [
        'villa_slug' => '',
        'name' => 'Christmas',
        'start_date' => now()->addDays(60)->toDateString(),
        'end_date' => now()->addDays(70)->toDateString(),
        'price' => '14000',
    ])->assertSessionHasNoErrors();

    expect(SeasonalRate::firstOrFail()->villa_slug)->toBeNull();

    $this->actingAs($admin)->put(route('admin.availability.units.update'), ['units' => ['standard-villa' => 3]])
        ->assertSessionHasNoErrors();

    expect(Availability::units('standard-villa'))->toBe(3);
});

test('closed dates and seasonal rates can be edited and removed', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $block = BlockedDate::create(['villa_slug' => null, 'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(6)->toDateString()]);
    $season = SeasonalRate::create(['villa_slug' => null, 'name' => 'Old', 'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(6)->toDateString(), 'price' => 9500]);

    $this->actingAs($admin)->put(route('admin.availability.blocks.update', $block), [
        'villa_slug' => 'standard-villa', 'start_date' => now()->addDays(7)->toDateString(), 'end_date' => now()->addDays(8)->toDateString(), 'reason' => 'Moved',
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->put(route('admin.availability.seasons.update', $season), [
        'villa_slug' => '', 'name' => 'New', 'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(6)->toDateString(), 'price' => '10000',
    ])->assertSessionHasNoErrors();

    expect($block->fresh()->reason)->toBe('Moved')
        ->and($season->fresh()->name)->toBe('New');

    $this->actingAs($admin)->delete(route('admin.availability.blocks.destroy', $block))->assertSessionHasNoErrors();
    $this->actingAs($admin)->delete(route('admin.availability.seasons.destroy', $season))->assertSessionHasNoErrors();

    expect(BlockedDate::count())->toBe(0)->and(SeasonalRate::count())->toBe(0);
});

test('the last night cannot be before the first night', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('admin.availability.blocks.store'), [
        'start_date' => now()->addDays(9)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->assertSessionHasErrors('end_date');
});

test('only signed-in admins can use the availability pages', function () {
    $this->get(route('admin.availability.index'))->assertRedirect();
    $this->post(route('admin.availability.blocks.store'), [])->assertRedirect();
});
