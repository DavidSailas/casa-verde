<?php

use App\Models\HomeContent;
use Database\Seeders\YogaContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// The migrations already load the Yoga starting content into a fresh test database. Start every test
// from an empty Yoga table, so a test sees only what it adds itself (or the page's built-in defaults).
beforeEach(function () {
    HomeContent::where('section', 'like', 'yoga_%')->delete();
});

test('the yoga page shows every block of the retreat layout with its starting content', function () {
    $this->get('/yoga-reset')
        ->assertOk()
        ->assertSee('Breathe. Stretch. Reset.')
        ->assertSee('Reserve directly with our team.')
        ->assertSee('Sunrise Flow')
        ->assertSee('How a Yoga Reset day flows.')
        ->assertSee('Open classes')
        ->assertSee('A small resort on the cliff')
        ->assertSee('Impressions of Casa Verde')
        ->assertSee('What a Yoga Reset includes')
        ->assertSee('Do I need yoga experience?')
        ->assertSee('application/ld+json', false);
});

test('the layout switches hide a block and the position moves it', function () {
    $this->seed(YogaContentSeeder::class);

    HomeContent::where('section', 'yoga_layout')->where('subtitle', 'gallery')->update(['active' => false]);

    $this->get('/yoga-reset')
        ->assertOk()
        ->assertDontSee('Impressions of Casa Verde')
        ->assertSee('A small resort on the cliff');

    // Move the package above the resort.
    HomeContent::where('section', 'yoga_layout')->where('subtitle', 'package')->update(['sort_order' => 0]);
    HomeContent::where('section', 'yoga_layout')->where('subtitle', 'resort')->update(['sort_order' => 50]);

    $this->get('/yoga-reset')
        ->assertOk()
        ->assertSeeInOrder(['What a Yoga Reset includes', 'A small resort on the cliff']);
});

test('the guest quote stays hidden until it is switched on', function () {
    $this->seed(YogaContentSeeder::class);

    $this->get('/yoga-reset')->assertOk()->assertDontSee('Guest name');

    HomeContent::where('section', 'yoga_layout')->where('subtitle', 'quote')->update(['active' => true]);

    $this->get('/yoga-reset')->assertOk()->assertSee('Guest name');
});

test('day tabs split the timeline and slots without a day appear on every day', function () {
    HomeContent::create(['section' => 'yoga_days', 'title' => 'Welcome', 'description' => 'Arrive and settle in', 'sort_order' => 1, 'active' => true]);
    HomeContent::create(['section' => 'yoga_days', 'title' => 'Ground', 'description' => 'Movement and reflection', 'sort_order' => 2, 'active' => true]);
    HomeContent::create(['section' => 'yoga_schedule', 'title' => '3:00 PM', 'subtitle' => 'Arrival on day one', 'price' => 1, 'sort_order' => 1, 'active' => true]);
    HomeContent::create(['section' => 'yoga_schedule', 'title' => '7:30 AM', 'subtitle' => 'Morning yoga every day', 'sort_order' => 2, 'active' => true]);

    $response = $this->get('/yoga-reset')
        ->assertOk()
        ->assertSee('role="tablist"', false)
        ->assertSee('Day 2')
        ->assertSee('Arrival on day one')
        ->assertSee('Movement and reflection');

    expect(substr_count($response->getContent(), 'Morning yoga every day'))->toBe(2)
        ->and(substr_count($response->getContent(), 'Arrival on day one'))->toBe(1);
});

test('class sessions with the same day label are grouped in one card', function () {
    HomeContent::create(['section' => 'yoga_classes', 'title' => 'Wed 2 Dec', 'subtitle' => '07:30 · Morning yoga', 'button_text' => 'Register', 'sort_order' => 1, 'active' => true]);
    HomeContent::create(['section' => 'yoga_classes', 'title' => 'Wed 2 Dec', 'subtitle' => '16:00 · Afternoon yoga', 'button_text' => 'Register', 'sort_order' => 2, 'active' => true]);

    $response = $this->get('/yoga-reset')
        ->assertOk()
        ->assertSee('07:30 · Morning yoga')
        ->assertSee('16:00 · Afternoon yoga')
        ->assertDontSee('Any time · Private session');

    expect(substr_count($response->getContent(), 'class="yg-day-card"'))->toBe(1);
});

test('the second banner button can be changed or switched off', function () {
    $this->get('/yoga-reset')->assertOk()->assertSee('What is included');

    $row = HomeContent::create(['section' => 'yoga_hero_link', 'button_text' => 'See the programme', 'button_url' => '#programme', 'sort_order' => 1, 'active' => true]);

    $this->get('/yoga-reset')->assertOk()->assertSee('See the programme')->assertDontSee('What is included');

    $row->update(['active' => false]);

    $this->get('/yoga-reset')->assertOk()->assertDontSee('See the programme')->assertDontSee('What is included');
});

test('optional cards appear once they are added in the admin', function () {
    $this->get('/yoga-reset')->assertOk()->assertDontSee('Next date')->assertDontSee('Share it with friends');

    HomeContent::create(['section' => 'yoga_next', 'title' => 'Next date: February 2027', 'description' => 'We run again in February.', 'button_text' => 'Register', 'sort_order' => 1, 'active' => true]);
    HomeContent::create(['section' => 'yoga_share', 'title' => 'Share it with friends', 'description' => 'Our programme as a PDF.', 'sort_order' => 1, 'active' => true]);

    $this->get('/yoga-reset')->assertOk()->assertSee('Next date: February 2027')->assertSee('Share it with friends');
});

test('questions edited in the admin replace the defaults', function () {
    HomeContent::create([
        'section' => 'yoga_faq',
        'title' => 'Is there parking?',
        'description' => 'Yes, free parking at the main entrance.',
        'sort_order' => 1,
        'active' => true,
    ]);

    $this->get('/yoga-reset')
        ->assertOk()
        ->assertSee('Is there parking?')
        ->assertSee('Yes, free parking at the main entrance.')
        ->assertDontSee('Do I need yoga experience?');
});

test('the seeder loads the layout and content into the admin without duplicating it', function () {
    $this->seed(YogaContentSeeder::class);
    $this->seed(YogaContentSeeder::class);

    expect(HomeContent::where('section', 'yoga_layout')->count())->toBe(count(config('yoga.layout')))
        ->and(HomeContent::where('section', 'yoga_faq')->count())->toBe(count(config('yoga.faq.items')))
        ->and(HomeContent::where('section', 'yoga_classes')->count())->toBe(count(config('yoga.classes.sessions')));
});
