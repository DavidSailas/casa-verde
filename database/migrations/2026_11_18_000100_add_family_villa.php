<?php

use App\Models\HomeContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Adds the Family Villa next to the Standard and Premium villas: a card on the Home and Villas pages,
 * a photo gallery, a Book page entry and a full detail page, all from one entry in Admin > Villas.
 *
 * The price, number of bedrooms, guests and bathrooms are starting values only. Change them in
 * Admin > Villas > Family Villa (price, "Bedrooms and sleeping arrangement", "Sleeps", "Bathrooms")
 * and every page follows. Nothing that already exists is overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = HomeContent::where('section', 'villas')->get()
            ->contains(fn (HomeContent $villa): bool => Str::slug((string) $villa->title) === 'family-villa');

        if (! $exists) {
            HomeContent::create([
                'section' => 'villas',
                'title' => 'Family Villa',
                'subtitle' => 'Space for everyone. Time together.',
                'description' => 'Room for the whole family under one roof. Separate bedrooms, a comfortable living area and everything you need for a relaxed island stay.',
                'image' => 'images/villas/family-card.jpg',
                // Temporary price: change it in the admin whenever the real rate is decided.
                'price' => 14000,
                'features' => [
                    'Separate bedrooms for the whole family',
                    'Cosy living area to relax together',
                    'Bunk beds the kids will love',
                ],
                'gallery' => array_map(fn (int $n): string => "images/villas/family-{$n}.jpg", range(1, 10)),
                'details' => [
                    'intro' => '',
                    'story_title' => "Designed for families.\nMade for togetherness.",
                    'story_text' => "Warm interiors, natural materials and a bright living area give everyone room to relax, play and be together.\n\nSeparate bedrooms mean privacy for the grown-ups and an adventure for the little ones.",
                    'highlights' => [
                        ['icon' => 'villa', 'icon2' => '', 'label' => 'Private Villa'],
                        ['icon' => 'beds', 'icon2' => '', 'label' => 'Family Bedrooms'],
                        ['icon' => 'bath', 'icon2' => '', 'label' => 'Spacious Bathrooms'],
                        ['icon' => 'family', 'icon2' => '', 'label' => 'Cosy Living Area'],
                        ['icon' => 'wifi', 'icon2' => '', 'label' => 'High-Speed Wi-Fi'],
                    ],
                    // Temporary sleeping arrangement: edit or add rooms in the admin.
                    'bedrooms' => [
                        ['name' => 'Bedroom 1', 'beds' => '1 double bed'],
                        ['name' => 'Bedroom 2', 'beds' => '1 double bed'],
                        ['name' => 'Bedroom 3', 'beds' => 'Bunk bed (sleeps 2)'],
                    ],
                    'capacity' => ['guests' => 6, 'bathrooms' => 2],
                    'amenities' => [
                        "Air Conditioning\nFlat-Screen TV\nLiving Area with Sofa\nWardrobe & Storage",
                        "Double Beds with Premium Linens\nBunk Beds for the Kids\nHot & Cold Shower\nPremium Bath Amenities",
                        "High-Speed Wi-Fi\nCoffee & Tea Station\nDaily Housekeeping",
                    ],
                    'layout' => ['interior' => '', 'terrace' => ''],
                    'hero' => 'images/villas/family-hero.jpg',
                    'plan' => null,
                ],
                'button_text' => 'View Family Villa',
                'button_url' => '#',
                'sort_order' => ((int) HomeContent::where('section', 'villas')->max('sort_order')) + 1,
                'active' => true,
            ]);
        }

        // The page heading said "Two distinctive villa styles"; only reword it if nobody has edited it.
        HomeContent::where('section', 'villas_header')
            ->where('description', 'Two distinctive villa styles. One unforgettable Casa Verde experience.')
            ->update(['description' => 'Three villa styles for every kind of stay. One unforgettable Casa Verde experience.']);
    }

    public function down(): void
    {
        // Intentionally empty: removing content is not something to undo silently.
    }
};
