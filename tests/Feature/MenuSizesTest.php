<?php

use App\Models\HomeContent;
use App\Models\User;
use App\Support\ImageUploader;
use App\Support\MenuSizes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->stored = [];
});

afterEach(function () {
    foreach ($this->stored as $path) {
        @unlink(public_path($path));
    }
});

function foodCover(): HomeContent
{
    return HomeContent::where('section', MenuSizes::FOOD)->firstOrFail();
}

function drinksSettings(): HomeContent
{
    return HomeContent::firstOrCreate(
        ['section' => MenuSizes::DRINKS],
        ['title' => 'Drinks Menu', 'sort_order' => 1, 'active' => true],
    );
}

/** @param array<string, string> $sizes */
function saveSizes(HomeContent $row, array $sizes, ?User $admin = null)
{
    return test()->actingAs($admin ?? test()->admin)->put(route('admin.content.update', $row), [
        'section' => $row->section,
        'title' => $row->title,
        'active' => 1,
        'sizes_present' => 1,
        'sizes' => $sizes,
    ]);
}

test('nothing changes on the site until a size is chosen', function () {
    $this->get('/dining')
        ->assertOk()
        ->assertDontSee('--mb-icon-scale', false)
        ->assertDontSee('--mb-legend-scale', false)
        ->assertDontSee('--mb-photo-scale', false)
        ->assertDontSee('--dm-cols', false)
        ->assertDontSee('--dm-logo-scale', false);
});

test('larger icons chosen for the food menu reach every page of the book', function () {
    saveSizes(foodCover(), ['photo' => 'medium', 'icon' => 'large', 'legend' => 'small'])->assertRedirect();

    expect(foodCover()->details['display'])->toBe(['photo' => 'medium', 'icon' => 'large', 'legend' => 'small']);

    $this->get('/dining')
        ->assertOk()
        ->assertSee('--mb-icon-scale: 1.25', false)
        ->assertSee('--mb-legend-scale: 0.8', false);
});

test('the dish photo size of the whole menu is applied to each dish', function () {
    saveSizes(foodCover(), ['photo' => 'small'])->assertRedirect();

    $this->get('/dining')->assertOk()->assertSee('--mb-photo-scale: 0.85', false);
});

test('one dish can be made larger than the rest, within a safe limit', function () {
    $dish = HomeContent::where('section', MenuSizes::DISH)->where('title', 'Salmon Steak')->firstOrFail();

    saveSizes($dish, ['photo' => 'larger'])->assertRedirect();

    $this->get('/dining')->assertOk()->assertSee('--mb-photo-scale: 1.15', false);

    // Large menu x larger dish would be 1.32: it stops at 1.3 so the page keeps room for its text.
    saveSizes(foodCover(), ['photo' => 'large'])->assertRedirect();

    $this->get('/dining')->assertOk()->assertSee('--mb-photo-scale: 1.3', false);
});

test('drink photos and the logo of the drinks menu can be resized', function () {
    $settings = drinksSettings();

    saveSizes($settings, ['photo' => 'compact', 'logo' => 'large'])->assertRedirect();

    $this->get('/dining')
        ->assertOk()
        ->assertSee('--dm-cols: 4', false)
        ->assertSee('--dm-logo-scale: 1.3', false);

    saveSizes($settings, ['photo' => 'standard', 'logo' => 'medium'])->assertRedirect();

    $this->get('/dining')->assertOk()->assertDontSee('--dm-cols', false)->assertDontSee('--dm-logo-scale', false);
});

test('unknown sizes are ignored and nothing else can be saved through the size box', function () {
    saveSizes(foodCover(), ['icon' => 'gigantic', 'photo' => 'small', 'evil' => 'large'])->assertRedirect();

    expect(foodCover()->details['display'])->toBe(['photo' => 'small']);
});

test('saving the sizes keeps what else is stored in the entry', function () {
    $dish = HomeContent::where('section', MenuSizes::DISH)->firstOrFail();
    $dish->update(['details' => ['note' => 'keep me']]);

    saveSizes($dish, ['photo' => 'smaller'])->assertRedirect();

    expect($dish->fresh()->details)->toBe(['note' => 'keep me', 'display' => ['photo' => 'smaller']]);
});

test('other entries ignore the size box', function () {
    $article = HomeContent::create(['section' => 'journal_articles', 'title' => 'A story', 'sort_order' => 1, 'active' => true]);

    saveSizes($article, ['photo' => 'large'])->assertRedirect();

    expect($article->fresh()->details)->toBeNull();
});

test('the form shows the Display size box with a preview only for the menu entries', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', foodCover()))
        ->assertOk()
        ->assertSee('Display size')
        ->assertSee('name="sizes[icon]"', false)
        ->assertSee('data-size-preview', false)
        ->assertSee('Back to standard sizes');

    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', drinksSettings()))
        ->assertOk()
        ->assertSee('name="sizes[logo]"', false);

    $dish = HomeContent::where('section', MenuSizes::DISH)->firstOrFail();

    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', $dish))
        ->assertOk()
        ->assertSee('Photo size of this dish');

    $article = HomeContent::create(['section' => 'journal_articles', 'title' => 'A story', 'sort_order' => 1, 'active' => true]);

    $this->actingAs($this->admin)
        ->get(route('admin.content.edit', $article))
        ->assertOk()
        ->assertDontSee('Display size');
});

test('the saved choice is selected when the form is opened again', function () {
    saveSizes(foodCover(), ['icon' => 'large'])->assertRedirect();

    $html = $this->actingAs($this->admin)->get(route('admin.content.edit', foodCover()))->assertOk()->getContent();

    preg_match('/<input type="radio"[^>]*name="sizes\[icon\]" value="large"[^>]*>/s', $html, $match);

    expect($match[0] ?? '')->toContain('checked');
});

test('menu uploads are kept small: dish and drink photos at 1200px, icons at 256px', function () {
    expect(MenuSizes::uploadLimit('dining_page_food_menu_dishes', 'image'))->toBe(1200)
        ->and(MenuSizes::uploadLimit('dining_page_drinks_items', 'image'))->toBe(1200)
        ->and(MenuSizes::uploadLimit('dining_page_food_menu_icons', 'image'))->toBe(256)
        ->and(MenuSizes::uploadLimit('dining_page_drinks_menu', 'icon_image'))->toBe(256)
        ->and(MenuSizes::uploadLimit('dining_page_food_menu', 'image'))->toBeNull() // the full-page cover keeps its detail
        ->and(MenuSizes::uploadLimit('journal_articles', 'image'))->toBeNull();
});

test('a dish photo and an icon uploaded in the admin are shrunk to the menu limits', function () {
    if (! ImageUploader::canConvert()) {
        $this->markTestSkipped('The GD extension with WebP support is needed.');
    }

    $this->actingAs($this->admin)->post(route('admin.content.store'), [
        'section' => MenuSizes::DISH,
        'title' => 'Test dish',
        'image' => UploadedFile::fake()->image('plate.jpg', 2400, 1800),
    ])->assertRedirect();

    $this->actingAs($this->admin)->post(route('admin.content.store'), [
        'section' => 'dining_page_food_menu_icons',
        'title' => 'testicon',
        'image' => UploadedFile::fake()->image('icon.png', 800, 800),
    ])->assertRedirect();

    $dish = HomeContent::where('title', 'Test dish')->firstOrFail();
    $icon = HomeContent::where('title', 'testicon')->firstOrFail();
    $this->stored = [$dish->image, $icon->image];

    expect($dish->image)->toEndWith('.webp')
        ->and(getimagesize(public_path($dish->image))[0])->toBe(1200)
        ->and(getimagesize(public_path($icon->image))[0])->toBe(256);
});
