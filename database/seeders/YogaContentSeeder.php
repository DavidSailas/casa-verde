<?php

namespace Database\Seeders;

use App\Models\HomeContent;
use Illuminate\Database\Seeder;

class YogaContentSeeder extends Seeder
{
    /**
     * Load the Yoga Reset page mock-up content into the admin without touching
     * any other content. Safe to run more than once.
     */
    public function run(): void
    {
        $yoga = config('yoga');

        $this->seed('yoga_hero', 1, [
            'subtitle' => $yoga['hero']['eyebrow'],
            'title' => $yoga['hero']['title'],
            'description' => $yoga['hero']['text'],
            'image' => $yoga['hero']['image'],
            'button_text' => $yoga['hero']['button_text'],
            'button_url' => $yoga['hero']['button_url'],
        ]);

        $this->seed('yoga_hero_link', 1, [
            'button_text' => $yoga['hero_link']['text'],
            'button_url' => $yoga['hero_link']['url'],
        ]);

        foreach ($yoga['chips'] as $index => $chip) {
            $this->seed('yoga_hero_chips', $index + 1, [
                'title' => $chip['text'],
                'subtitle' => $chip['style'],
            ]);
        }

        foreach ($yoga['facts'] as $index => $fact) {
            $this->seed('yoga_hero_facts', $index + 1, ['title' => $fact]);
        }

        $this->seed('yoga_notice', 1, [
            'subtitle' => $yoga['notice']['label'],
            'title' => $yoga['notice']['title'],
            'description' => $yoga['notice']['text'],
        ]);

        foreach ($yoga['stats'] as $index => $stat) {
            $this->seed('yoga_stats', $index + 1, [
                'title' => $stat['title'],
                'subtitle' => $stat['eyebrow'],
                'icon' => $stat['icon'],
            ]);
        }

        $this->seed('yoga_intro', 1, [
            'subtitle' => $yoga['intro']['eyebrow'],
            'title' => $yoga['intro']['title'],
            'description' => $yoga['intro']['text'],
        ]);

        foreach ($yoga['types'] as $index => $type) {
            $this->seed('yoga_types', $index + 1, [
                'title' => $type['title'],
                'subtitle' => $type['eyebrow'] ?? null,
                'description' => $type['text'],
                'image' => $type['image'],
                'icon' => $type['icon'],
                'button_text' => $type['button_text'],
                'button_url' => $type['button_url'],
            ]);
        }

        $this->seed('yoga_schedule_header', 1, [
            'subtitle' => $yoga['schedule']['eyebrow'],
            'title' => $yoga['schedule']['title'],
        ]);

        foreach ($yoga['schedule']['items'] as $index => $slot) {
            $this->seed('yoga_schedule', $index + 1, [
                'title' => $slot['title'],
                'subtitle' => $slot['eyebrow'],
                'description' => $slot['text'],
            ]);
        }

        $this->seed('yoga_flow', 1, [
            'title' => $yoga['flow']['title'],
            'subtitle' => $yoga['flow']['note'],
            'description' => $yoga['flow']['text'],
        ]);

        $this->seed('yoga_care', 1, [
            'title' => $yoga['care']['title'],
            'description' => $yoga['care']['note'],
            'features' => $yoga['care']['items'],
        ]);

        $this->seed('yoga_classes_info', 1, [
            'title' => $yoga['personal']['title'],
            'description' => $yoga['personal']['text'],
            'subtitle' => $yoga['classes']['note'],
            'features' => [$yoga['classes']['small']],
        ]);

        foreach ($yoga['classes']['sessions'] as $index => $session) {
            $this->seed('yoga_classes', $index + 1, [
                'title' => $session['group'],
                'subtitle' => $session['name'],
                'description' => $session['meta'],
                'button_text' => $session['button_text'],
                'button_url' => $session['button_url'],
            ]);
        }

        $this->seed('yoga_resort', 1, [
            'title' => $yoga['resort']['title'],
            'description' => $yoga['resort']['text'],
        ]);

        foreach ($yoga['resort']['photos'] as $index => $photo) {
            $this->seed('yoga_resort_photos', $index + 1, [
                'title' => $photo['caption'],
                'image' => $photo['image'],
            ]);
        }

        $this->seed('yoga_spotlight', 1, [
            'subtitle' => $yoga['resort']['spotlight']['badge'],
            'title' => $yoga['resort']['spotlight']['title'],
            'description' => $yoga['resort']['spotlight']['text'],
            'image' => $yoga['resort']['spotlight']['image'],
        ]);

        $this->seed('yoga_gallery_header', 1, [
            'title' => $yoga['gallery']['title'],
            'description' => $yoga['gallery']['text'],
        ]);

        foreach ($yoga['gallery']['items'] as $index => $photo) {
            $this->seed('yoga_gallery', $index + 1, [
                'title' => $photo['caption'],
                'image' => $photo['image'],
            ]);
        }

        $this->seed('yoga_teacher', 1, [
            'title' => $yoga['teacher']['title'],
            'subtitle' => $yoga['teacher']['role'],
            'description' => $yoga['teacher']['text'],
            'image' => $yoga['teacher']['image'],
            'button_text' => $yoga['teacher']['caption'],
            'features' => $yoga['teacher']['styles'],
        ]);

        $this->seed('yoga_package_header', 1, ['title' => $yoga['package']['title']]);

        foreach ($yoga['package']['columns'] as $index => $column) {
            $this->seed('yoga_package', $index + 1, [
                'title' => $column['title'],
                'subtitle' => $column['style'],
                'features' => $column['items'],
            ]);
        }

        $this->seed('yoga_price', 1, [
            'title' => $yoga['package']['price']['title'],
            'subtitle' => $yoga['package']['price']['big'],
            'description' => $yoga['package']['price']['text'],
            'features' => $yoga['package']['price']['bullets'],
            'button_text' => $yoga['package']['price']['button_text'],
            'button_url' => $yoga['package']['price']['button_url'],
        ]);

        $position = 0;

        foreach ($yoga['layout'] as $key => $block) {
            // The guest quote is not part of the new layout; it stays one switch away.
            $this->seed('yoga_layout', ++$position, [
                'title' => $block['title'],
                'subtitle' => $key,
                'active' => $key !== 'quote',
            ]);
        }

        $this->seed('yoga_features_header', 1, ['title' => $yoga['features']['title']]);

        foreach ($yoga['features']['items'] as $index => $feature) {
            $this->seed('yoga_features', $index + 1, [
                'title' => $feature['title'],
                'description' => $feature['text'],
                'icon' => $feature['icon'],
            ]);
        }

        $this->seed('yoga_quote', 1, [
            'title' => $yoga['quote']['title'],
            'subtitle' => $yoga['quote']['eyebrow'],
            'description' => $yoga['quote']['text'],
        ]);

        $this->seed('yoga_faq_header', 1, [
            'subtitle' => $yoga['faq']['eyebrow'],
            'title' => $yoga['faq']['title'],
        ]);

        foreach ($yoga['faq']['items'] as $index => $entry) {
            $this->seed('yoga_faq', $index + 1, [
                'title' => $entry['title'],
                'description' => $entry['text'],
            ]);
        }

        foreach ($yoga['cta']['buttons'] as $index => $button) {
            $this->seed('yoga_cta', $index + 1, [
                'title' => $index === 0 ? $yoga['cta']['title'] : null,
                'image' => $index === 0 ? $yoga['cta']['image'] : null,
                'button_text' => $button['text'],
                'button_url' => $button['url'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function seed(string $section, int $sortOrder, array $attributes): void
    {
        HomeContent::firstOrCreate(
            ['section' => $section, 'sort_order' => $sortOrder],
            $attributes + ['active' => true],
        );
    }
}
