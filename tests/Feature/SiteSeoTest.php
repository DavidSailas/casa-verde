<?php

use App\Models\HomeContent;
use App\Models\User;
use App\Support\SiteSeo;
use Database\Seeders\SiteSeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every public page keeps its usual browser title until the admin changes it', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('<title>Contact Us | Casa Verde Cliff Resort &amp; Spa</title>', false);

    $this->get('/villas')
        ->assertOk()
        ->assertSee('<title>Our Villas | Casa Verde Cliff Resort &amp; Spa</title>', false);

    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Casa Verde Cliff Resort &amp; Spa</title>', false);
});

test('every public page carries a description and link-preview tags', function () {
    foreach (['/', '/villas', '/wellness', '/dining', '/contact', '/about', '/gallery'] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<link rel="canonical"', false);
    }
});

test('the admin can change the title and description of a page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    // Opening the admin loads the starting entries.
    $this->actingAs($admin)->get(route('admin.content.index', ['group' => 'site']))
        ->assertOk()
        ->assertSee('Search and sharing');

    $contact = HomeContent::where('section', 'site_seo')->where('title', 'Contact Us')->firstOrFail();

    $contact->update([
        'subtitle' => 'Reach our team',
        'description' => 'Write to us and we will answer within a day.',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('<title>Reach our team | Casa Verde Cliff Resort &amp; Spa</title>', false)
        ->assertSee('content="Write to us and we will answer within a day."', false);
});

test('the name after every title comes from the Whole site entry', function () {
    $this->seed(SiteSeoSeeder::class);

    HomeContent::where('section', 'site_seo')->where('title', 'Whole site')->update(['subtitle' => 'Casa Verde']);

    $this->get('/contact')->assertSee('<title>Contact Us | Casa Verde</title>', false);
});

test('switching a page entry off returns to the wording built into the page', function () {
    $this->seed(SiteSeoSeeder::class);

    HomeContent::where('section', 'site_seo')->where('title', 'Contact Us')
        ->update(['subtitle' => 'Something else', 'active' => false]);

    $this->get('/contact')->assertSee('<title>Contact Us | Casa Verde Cliff Resort &amp; Spa</title>', false);
});

test('a Google Analytics ID is added to every page once it is entered, and only when it is valid', function () {
    $this->seed(SiteSeoSeeder::class);

    $this->get('/')->assertDontSee('googletagmanager.com', false);

    HomeContent::where('section', 'site_seo')->where('title', 'Whole site')->update(['button_text' => 'g-abc123xyz9']);

    $this->get('/')->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123XYZ9', false);

    HomeContent::where('section', 'site_seo')->where('title', 'Whole site')->update(['button_text' => "'); alert(1); ('"]);

    $this->get('/')->assertDontSee('googletagmanager.com', false);
});

test('analytics IDs are checked strictly', function () {
    expect(SiteSeo::analyticsId(' g-abc123xyz9 '))->toBe('G-ABC123XYZ9')
        ->and(SiteSeo::analyticsId('UA-1234-1'))->toBe('')
        ->and(SiteSeo::analyticsId("G-ABC'); x('"))->toBe('')
        ->and(SiteSeo::analyticsId(''))->toBe('');
});

test('a villa page uses the villa name and a short description of the villa', function () {
    $villa = HomeContent::where('section', 'villas')->where('active', true)->orderBy('sort_order')->first();

    if (! $villa) {
        $this->markTestSkipped('No villa in the starting content.');
    }

    $this->get(route('villas.show', \Illuminate\Support\Str::slug($villa->title)))
        ->assertOk()
        ->assertSee('<title>'.e($villa->title).' | Casa Verde Cliff Resort &amp; Spa</title>', false);
});

test('a page can be kept out of search results', function () {
    $html = view('layouts.seo', ['seoTitle' => 'Request received', 'seoNoindex' => true])->render();

    expect($html)
        ->toContain('<meta name="robots" content="noindex">')
        ->not->toContain('rel="canonical"')
        ->toContain('<title>Request received | Casa Verde Cliff Resort &amp; Spa</title>');
});
