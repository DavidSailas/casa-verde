<?php

use App\Models\HomeContent;
use App\Models\User;
use App\Support\VillaDetails;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);

    foreach (['Standard Villa' => 1, 'Premium Villa' => 2] as $title => $order) {
        HomeContent::create([
            'section' => 'villas', 'title' => $title, 'subtitle' => 'Test villa', 'image' => 'images/logo.png',
            'price' => 5000, 'sort_order' => $order, 'active' => true,
        ]);
    }
});

function familyBundlePayload(array $override = []): array
{
    return array_replace_recursive([
        'section' => 'villas',
        'title' => 'Family Bundle',
        'subtitle' => 'Space for everyone.',
        'description' => 'Two connected villas for the whole family.',
        'features' => "Sleeps six\nTwo bathrooms\nShared terrace",
        'price' => 16000,
        'sort_order' => 3,
        'active' => 1,
        'details_present' => 1,
        'details' => [
            'intro' => 'Everything a family needs, side by side.',
            'story_title' => "Designed for families.\nMade for togetherness.",
            'story_text' => "First paragraph.\n\nSecond paragraph.",
            'highlights' => [
                ['icon' => 'family', 'label' => 'Sleeps Six'],
                ['icon' => 'beds', 'icon2' => 'queen-bed', 'label' => 'Three Beds'],
                ['icon' => '', 'label' => ''],
            ],
            'amenities' => ["Air Conditioning\nKitchenette", "Two Bathrooms", ''],
            'layout' => ['interior' => 80, 'terrace' => 25],
        ],
    ], $override);
}

test('a new villa added in the admin appears on the Home and Villas pages', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.content.store'), familyBundlePayload())
        ->assertRedirect();

    $this->get(route('villas.index'))->assertOk()->assertSee('Family Bundle')->assertSee('Space for everyone.');
    $this->get('/')->assertOk()->assertSee('Family Bundle');
});

test('the new villa gets a full detail page from the admin alone', function () {
    $this->actingAs($this->admin)->post(route('admin.content.store'), familyBundlePayload());

    $this->get(route('villas.show', 'family-bundle'))
        ->assertOk()
        ->assertSee('Everything a family needs, side by side.')
        ->assertSee('Designed for families.', false)
        ->assertSee('Second paragraph.')
        ->assertSee('Sleeps Six')
        ->assertSee('Three Beds')
        ->assertSee('Kitchenette')
        ->assertSee('Two Bathrooms')
        ->assertSee('80 sqm')
        ->assertSee('25 sqm')
        ->assertSee('105 sqm');
});

test('the new villa can be booked online like the others', function () {
    $this->actingAs($this->admin)->post(route('admin.content.store'), familyBundlePayload());

    $this->get(route('booking.villa', 'family-bundle'))->assertOk()->assertSee('Family Bundle');
});

test('details are worked out from the saved entry and fall back to the description', function () {
    $this->actingAs($this->admin)->post(route('admin.content.store'), familyBundlePayload([
        'details' => ['intro' => ''],
    ]));

    $villa = HomeContent::where('section', 'villas')->where('title', 'Family Bundle')->firstOrFail();
    $detail = VillaDetails::for($villa);

    expect($detail['custom'])->toContain('highlights', 'amenities', 'layout')
        ->not->toContain('intro')
        ->and($detail['highlights'])->toHaveCount(2)
        ->and($detail['highlights'][1])->toMatchArray(['icon' => 'beds', 'icon2' => 'queen-bed', 'label' => 'Three Beds'])
        ->and($detail['layout']['total'])->toBe(105.0)
        ->and($detail['amenities'])->toHaveCount(2);

    $this->get(route('villas.show', 'family-bundle'))->assertSee('Two connected villas for the whole family.');
});

test('a villa with no detail content still shows a clean page', function () {
    $this->actingAs($this->admin)->post(route('admin.content.store'), [
        'section' => 'villas', 'title' => 'Garden Suite', 'subtitle' => 'Quiet.', 'price' => 7000,
        'sort_order' => 4, 'active' => 1, 'details_present' => 1,
    ])->assertRedirect();

    $this->get(route('villas.show', 'garden-suite'))->assertOk()->assertSee('Garden Suite');
});

test('the existing villas keep their starting content and show it in the admin form', function () {
    $standard = HomeContent::where('section', 'villas')->where('title', 'Standard Villa')->firstOrFail();

    expect(VillaDetails::for($standard)['highlights'])->not->toBeEmpty();

    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', $standard))
        ->assertOk()
        ->assertSee('Villa detail page')
        ->assertSee('Private Villa')
        ->assertSee('Air Conditioning');
});

test('saving another kind of entry never touches villa details', function () {
    $this->actingAs($this->admin)->post(route('admin.content.store'), familyBundlePayload());
    $villa = HomeContent::where('section', 'villas')->where('title', 'Family Bundle')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.content.update', $villa), [
        'section' => 'villas', 'title' => 'Family Bundle', 'price' => 17000,
    ])->assertRedirect();

    expect($villa->fresh()->details['intro'])->toBe('Everything a family needs, side by side.');
});

test('the Villas page photo pop-up has thumbnails and booking buttons', function () {
    // Two photos that exist on disk, so the villa gets a gallery.
    HomeContent::where('section', 'villas')->where('title', 'Standard Villa')->update(['gallery' => ['images/logo.png', 'images/logo-badge.png']]);

    $this->get(route('villas.index'))
        ->assertOk()
        ->assertSee('data-vm-go="1"', false)
        ->assertSee('vm-thumbs', false)
        ->assertSee(route('booking.villa', 'standard-villa'), false)
        ->assertSee(route('villas.show', 'standard-villa'), false);
});

test('the new-entry form for Villas explains itself and offers the detail page boxes', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.content.create', ['section' => 'villas']))
        ->assertOk()
        ->assertSee('Family Bundle')
        ->assertSee('Villa detail page')
        ->assertSee('Add another highlight')
        ->assertSee('Gallery photos');
});

function addFamilyVilla(): void
{
    (require database_path('migrations/2026_11_18_000100_add_family_villa.php'))->up();
}

test('the Family Villa is added once, after the existing villas, with a temporary price', function () {
    // The test database already ran the migration (before this test's villas existed), so start clean.
    HomeContent::where('section', 'villas')->where('title', 'Family Villa')->delete();

    addFamilyVilla();
    addFamilyVilla();

    $family = HomeContent::where('section', 'villas')->where('title', 'Family Villa')->get();

    expect($family)->toHaveCount(1)
        ->and($family->first()->sort_order)->toBe(3)
        ->and((float) $family->first()->price)->toBeGreaterThan(0)
        ->and($family->first()->gallery)->toHaveCount(10);

    foreach ($family->first()->gallery as $photo) {
        expect(file_exists(public_path($photo)))->toBeTrue();
    }
});

test('the Villas page shows the Family Villa with its quick facts and booking link', function () {
    addFamilyVilla();

    $this->get(route('villas.index'))
        ->assertOk()
        ->assertSee('Family Villa')
        ->assertSee('Sleeps 6')
        ->assertSee('3 Bedrooms')
        ->assertSee('2 Bathrooms')
        ->assertSee(route('booking.villa', 'family-villa'), false)
        ->assertSee(route('villas.show', 'family-villa'), false);

    // Villas without quick facts filled in are not given empty ones.
    $this->get(route('villas.index'))->assertDontSee('Sleeps 0')->assertDontSee('0 Bedrooms');
});

test('the Family Villa page lists every bedroom and can be booked', function () {
    addFamilyVilla();

    $this->get(route('villas.show', 'family-villa'))
        ->assertOk()
        ->assertSee('Bedrooms &amp; sleeping arrangement', false)
        ->assertSee('1 double bed')
        ->assertSee('Bunk bed (sleeps 2)');

    $this->get(route('booking.villa', 'family-villa'))->assertOk()->assertSee('Family Villa');
});

test('changing the bedrooms in the admin changes the count everywhere, and nothing breaks', function () {
    addFamilyVilla();
    $villa = HomeContent::where('section', 'villas')->where('title', 'Family Villa')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.content.update', $villa), [
        'section' => 'villas', 'title' => 'Family Villa', 'price' => 15500, 'details_present' => 1,
        'details' => [
            'bedrooms' => [
                ['name' => 'Master bedroom', 'beds' => '1 king bed'],
                ['name' => '', 'beds' => '2 single beds'],
                ['name' => '', 'beds' => ''],
                ['name' => 'Kids room', 'beds' => 'Bunk bed'],
                ['name' => 'Loft', 'beds' => 'Sofa bed'],
            ],
            'capacity' => ['guests' => 8, 'bathrooms' => 3],
        ],
    ])->assertRedirect();

    $detail = VillaDetails::for($villa->fresh());

    // Empty rows are ignored; a room without a name gets one; the bedroom count follows the list.
    expect($detail['bedrooms'])->toHaveCount(4)
        ->and($detail['bedrooms'][1]['name'])->toBe('Bedroom 2')
        ->and($detail['capacity'])->toBe(['guests' => 8, 'bedrooms' => 4, 'bathrooms' => 3]);

    $this->get(route('villas.index'))->assertOk()->assertSee('Sleeps 8')->assertSee('4 Bedrooms')->assertSee('15,500');
    $this->get(route('villas.show', 'family-villa'))->assertOk()->assertSee('Master bedroom')->assertSee('Kids room');
});

test('clearing the bedrooms hides that part instead of leaving an empty box', function () {
    addFamilyVilla();
    $villa = HomeContent::where('section', 'villas')->where('title', 'Family Villa')->firstOrFail();

    $this->actingAs($this->admin)->put(route('admin.content.update', $villa), [
        'section' => 'villas', 'title' => 'Family Villa', 'details_present' => 1,
        'details' => ['bedrooms' => [['name' => '', 'beds' => '']], 'capacity' => ['guests' => '', 'bathrooms' => '']],
    ])->assertRedirect();

    $this->get(route('villas.show', 'family-villa'))->assertOk()->assertDontSee('vs-rooms', false);
    $this->get(route('villas.index'))->assertOk()->assertSee('Family Villa')->assertDontSee('villa-facts', false);
});

test('the villa form in the admin offers the bedroom and capacity boxes', function () {
    addFamilyVilla();
    $villa = HomeContent::where('section', 'villas')->where('title', 'Family Villa')->firstOrFail();

    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', $villa))
        ->assertOk()
        ->assertSee('Bedrooms and sleeping arrangement')
        ->assertSee('Add another bedroom')
        ->assertSee('Sleeps (guests)')
        ->assertSee('1 double bed');
});
