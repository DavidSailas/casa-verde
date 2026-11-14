<?php

namespace App\Http\Controllers;

use App\Models\HomeContent;
use App\Support\VillaDetails;
use App\Support\VillaPhotos;
use Illuminate\Support\Str;

class VillaController extends Controller
{
    public function index()
    {
        $contents = HomeContent::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $site = $contents
            ->get('site', collect())
            ->first();

        $villasHeader = $contents
            ->get('villas_header', collect())
            ->first();

        $villas = $contents
            ->get('villas', collect());

        $villaPhotos = $this->photos($contents, $villas);

        // Quick facts (guests, bedrooms, bathrooms) for each card, from what is saved on the villa in the admin.
        $villaDetails = $villas->mapWithKeys(
            fn ($villa) => [Str::slug($villa->title) => VillaDetails::for($villa)]
        )->all();

        return view('villa', compact(
            'contents',
            'site',
            'villasHeader',
            'villas',
            'villaPhotos',
            'villaDetails'
        ));
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function photos($contents, $villas): array
    {
        return VillaPhotos::for($villas);
    }

    public function show(string $slug)
    {
        $contents = HomeContent::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $site = $contents->get('site', collect())->first();

        $villa = $contents
            ->get('villas', collect())
            ->first(fn ($item) => Str::slug($item->title) === $slug);

        abort_if(! $villa, 404);

        // Rich detail content (highlights, amenities, floor plan...): what is saved on the villa
        // entry in the admin, else the starting content in config/villas.php (see VillaDetails).
        $detail = VillaDetails::for($villa);

        $features = is_string($villa->features)
            ? json_decode($villa->features, true)
            : $villa->features;

        return view('villa-show', [
            'contents' => $contents,
            'site'     => $site,
            'villa'    => $villa,
            'detail'   => $detail,
            'intro'    => $detail['intro'] ?? $villa->description,
            'tagline'  => $detail['tagline'] ?? $villa->subtitle,
            // A wide photo set on the villa entry wins, then the older per-villa admin entry, then the starting photo.
            'heroImage' => (in_array('hero', $detail['custom'] ?? [], true) ? $detail['hero'] : null)
                ?: $contents->get('villas_detail_hero_' . str_replace('-', '_', $slug), collect())
                    ->first(fn ($item) => filled($item->image))?->image
                ?: ($detail['hero'] ?? $villa->image),
            'features' => $features ?? [],
        ]);
    }
}
