<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The drinks menu (opened by the EXPLORE DRINKS MENU button) used to live in config/drinks.php. This loads
     * its starting content into the admin (Dining > Drinks menu): the title and notes, the categories, the drinks
     * and the filter buttons, so drinks and their photos can be added, changed and hidden there. A section that
     * already has entries is left alone, so nothing edited in the admin is ever overwritten.
     */
    public function up(): void
    {
        $now = now();

        // ---- Title and notes ----
        if (! DB::table('home_contents')->where('section', 'dining_page_drinks_menu')->exists()) {
            foreach (array_values(config('pagecontent.dining_page_drinks_menu', [])) as $i => $row) {
                DB::table('home_contents')->insert($row + [
                    'section' => 'dining_page_drinks_menu',
                    'sort_order' => $i + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ---- Categories (in the order of the menu); the drinks point at them by id ----
        $categoryIds = [];

        if (! DB::table('home_contents')->where('section', 'dining_page_drinks_categories')->exists()) {
            foreach (array_values(config('pagecontent.dining_page_drinks_categories', [])) as $i => $row) {
                $categoryIds[strtolower(trim((string) $row['title']))] = DB::table('home_contents')->insertGetId($row + [
                    'section' => 'dining_page_drinks_categories',
                    'sort_order' => $i + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } else {
            foreach (DB::table('home_contents')->where('section', 'dining_page_drinks_categories')->get() as $row) {
                $categoryIds[strtolower(trim((string) $row->title))] = $row->id;
            }
        }

        // ---- Drinks ----
        if (! DB::table('home_contents')->where('section', 'dining_page_drinks_items')->exists()) {
            foreach (array_values(config('pagecontent.dining_page_drinks_items', [])) as $i => $row) {
                $row['features'] = json_encode(array_values((array) ($row['features'] ?? [])), JSON_UNESCAPED_UNICODE);
                $row['subtitle'] = (string) ($categoryIds[strtolower(trim((string) $row['subtitle']))] ?? $row['subtitle']);

                DB::table('home_contents')->insert($row + [
                    'section' => 'dining_page_drinks_items',
                    'sort_order' => $i + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ---- Filter buttons ----
        if (! DB::table('home_contents')->where('section', 'dining_page_drinks_filters')->exists()) {
            foreach (array_values(config('pagecontent.dining_page_drinks_filters', [])) as $i => $row) {
                DB::table('home_contents')->insert($row + [
                    'section' => 'dining_page_drinks_filters',
                    'sort_order' => $i + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('home_contents')->whereIn('section', [
            'dining_page_drinks_menu',
            'dining_page_drinks_categories',
            'dining_page_drinks_items',
            'dining_page_drinks_filters',
        ])->delete();
    }
};
