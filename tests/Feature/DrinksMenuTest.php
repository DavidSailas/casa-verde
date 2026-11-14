<?php

use App\Models\HomeContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

/** Sections of the drinks menu that tests start from scratch with. */
function clearDrinksMenu(): void
{
    HomeContent::whereIn('section', [
        'dining_page_drinks_categories',
        'dining_page_drinks_items',
        'dining_page_drinks_filters',
    ])->delete();
}

function makeDrinkCategory(string $name, string $layout = '', int $position = 1): HomeContent
{
    return HomeContent::create(['section' => 'dining_page_drinks_categories', 'title' => $name, 'subtitle' => $layout, 'sort_order' => $position, 'active' => true]);
}

/** @param array<string, mixed> $attributes */
function makeDrink(string $name, HomeContent|string|null $category, array $attributes = []): HomeContent
{
    return HomeContent::create($attributes + [
        'section' => 'dining_page_drinks_items',
        'title' => $name,
        'subtitle' => $category instanceof HomeContent ? (string) $category->id : (string) $category,
        'sort_order' => (int) HomeContent::where('section', 'dining_page_drinks_items')->max('sort_order') + 1,
        'active' => true,
    ]);
}

test('the drinks menu button opens the drinks menu loaded into the admin', function () {
    $this->get('/dining')
        ->assertOk()
        ->assertSee('data-drinks-menu="dm-drinks"', false)
        ->assertSee('id="dm-drinks"', false)
        ->assertSee('Casa Verde Sunset')
        ->assertSee('Mojito')
        ->assertSee('data-dm-filter="spirits"', false)
        ->assertSee('EXPLORE DRINKS MENU');
});

test('a new category and drink from the admin appear in the menu with the price and tags', function () {
    clearDrinksMenu();

    $wine = makeDrinkCategory('Wine by the glass');
    makeDrink('House Red', $wine, ['price' => 280, 'description' => 'Smooth and fruity.', 'features' => ['Wine']]);
    HomeContent::create(['section' => 'dining_page_drinks_filters', 'title' => 'Wine', 'sort_order' => 1, 'active' => true]);

    $this->get('/dining')
        ->assertOk()
        ->assertSee('Wine by the glass')
        ->assertSee('House Red')
        ->assertSee('Smooth and fruity.')
        ->assertSee('280')
        ->assertSee('data-dm-filter="wine"', false);
});

test('the title, note and currency of the menu come from the admin', function () {
    clearDrinksMenu();
    HomeContent::where('section', 'dining_page_drinks_menu')->update(['title' => 'Bar and Cellar', 'button_text' => 'USD', 'description' => 'Ask us about the daily special.']);
    makeDrink('House Red', makeDrinkCategory('Wine'), ['price' => 12]);

    $this->get('/dining')
        ->assertOk()
        ->assertSee('Bar and Cellar')
        ->assertSee('<span>USD</span> 12', false)
        ->assertSee('Ask us about the daily special.');
});

test('a filter nobody uses is not offered', function () {
    clearDrinksMenu();
    makeDrink('Iced Tea', makeDrinkCategory('Cold drinks'), ['features' => ['Alcohol-free']]);
    HomeContent::create(['section' => 'dining_page_drinks_filters', 'title' => 'Alcohol-free', 'sort_order' => 1, 'active' => true]);
    HomeContent::create(['section' => 'dining_page_drinks_filters', 'title' => 'Beer', 'sort_order' => 2, 'active' => true]);

    $this->get('/dining')
        ->assertOk()
        ->assertSee('data-dm-filter="alcohol-free"', false)
        ->assertDontSee('data-dm-filter="beer"', false);
});

test('a hidden drink is not shown', function () {
    clearDrinksMenu();

    $beer = makeDrinkCategory('Beer');
    makeDrink('Local Lager', $beer);
    makeDrink('Secret Stout', $beer, ['active' => false]);

    $this->get('/dining')->assertOk()->assertSee('Local Lager')->assertDontSee('Secret Stout');
});

test('a drink without a valid category is shown under More drinks instead of disappearing', function () {
    clearDrinksMenu();

    makeDrinkCategory('Beer');
    makeDrink('Mystery Drink', 'no-such-category');

    $this->get('/dining')->assertOk()->assertSee('Mystery Drink')->assertSee('More drinks');
});

test('a drink without a price has no dotted leader, and a drink with a price does', function () {
    clearDrinksMenu();

    $classics = makeDrinkCategory('Classics', 'list');
    makeDrink('Free Sample', $classics);

    $this->get('/dining')->assertOk()->assertSee('Free Sample')->assertDontSee('class="dm-dots"', false);

    makeDrink('Mojito Royale', $classics, ['price' => 350]);

    $this->get('/dining')->assertOk()->assertSee('class="dm-dots"', false)->assertSee('350');
});

test('a category whose drinks all have photos shows photo cards on its own', function () {
    clearDrinksMenu();

    $pictures = makeDrinkCategory('Pictured');
    makeDrink('Pretty Drink', $pictures, ['image' => 'images/dining/menu/dishes/carbonara.jpg']);

    $this->get('/dining')->assertOk()->assertSee('class="dm-cards"', false)->assertSee('images/dining/menu/dishes/carbonara.jpg', false);

    // The same drink in a category set to the compact list stays a list.
    HomeContent::where('id', $pictures->id)->update(['subtitle' => 'list']);

    $this->get('/dining')->assertOk()->assertDontSee('class="dm-cards"', false)->assertSee('class="dm-list"', false);
});

test('without any visible drink the drinks button stays a normal link', function () {
    HomeContent::where('section', 'dining_page_drinks_items')->update(['active' => false]);

    $this->get('/dining')
        ->assertOk()
        ->assertDontSee('data-drinks-menu', false)
        ->assertSee('EXPLORE DRINKS MENU');
});

test('the admin lists the drinks menu sections under Dining', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.content.index', ['group' => 'dining']))
        ->assertOk()
        ->assertSee('Drinks menu: categories')
        ->assertSee('Drinks menu: drinks')
        ->assertSee('Drinks menu: filters')
        ->assertSee('Drinks menu: title and notes');
});

test('a drink can be added in the admin with a photo and a category', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $category = HomeContent::where('section', 'dining_page_drinks_categories')->where('title', 'Classic Cocktails')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.content.create', ['section' => 'dining_page_drinks_items']))
        ->assertOk()
        ->assertSee('Drink name')
        ->assertSee('Classic Cocktails')
        ->assertSee('Tags (one per line)');

    $this->actingAs($admin)
        ->post(route('admin.content.store'), [
            'section' => 'dining_page_drinks_items',
            'title' => 'Dark and Stormy',
            'subtitle' => (string) $category->id,
            'price' => 320,
            'description' => 'Dark rum, ginger beer and lime.',
            'features' => "Spirits\nRum",
            'sort_order' => 99,
            'active' => 1,
            'image' => UploadedFile::fake()->image('dark-and-stormy.jpg', 800, 600),
        ])
        ->assertRedirect();

    $drink = HomeContent::where('section', 'dining_page_drinks_items')->where('title', 'Dark and Stormy')->firstOrFail();

    try {
        expect($drink->image)->toStartWith('images/')
            ->and(file_exists(public_path($drink->image)))->toBeTrue()
            ->and($drink->features)->toBe(['Spirits', 'Rum']);

        $this->get('/dining')
            ->assertOk()
            ->assertSee('Dark and Stormy')
            ->assertSee('Dark rum, ginger beer and lime.')
            ->assertSee('320');
    } finally {
        @unlink(public_path((string) $drink->image));
    }
});
