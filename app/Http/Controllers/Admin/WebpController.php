<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ImageUploader;
use App\Support\WebpMigration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * The "Convert old pictures to WebP" button on the Media library page. The pictures are done a few at a time
 * (the page asks again until everything is through), so a big library never runs into the server's time limit.
 */
class WebpController extends Controller
{
    private const MAX_SIDE = 2400;

    public function status(): JsonResponse
    {
        $pending = 0;
        $pendingBytes = 0;
        $done = 0;

        foreach (WebpMigration::pictures() as $picture) {
            if ($picture['ext'] === 'gif' && ImageUploader::isAnimatedGif($picture['path'])) {
                continue;
            }

            if (is_file(public_path(WebpMigration::webpPath($picture['relative'])))) {
                $done++;

                continue;
            }

            $pending++;
            $pendingBytes += (int) @filesize($picture['path']);
        }

        $webp = 0;

        if (is_dir(public_path('images'))) {
            foreach (File::allFiles(public_path('images')) as $file) {
                if (strtolower($file->getExtension()) === 'webp') {
                    $webp++;
                }
            }
        }

        return response()->json([
            'can' => ImageUploader::canConvert(),
            'webp' => $webp,
            'pending' => $pending,
            'pending_bytes' => $pendingBytes,
            'done' => $done,
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $data = $request->validate([
            'offset' => ['required', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        if (! ImageUploader::canConvert()) {
            return response()->json(['message' => 'This server cannot create WebP pictures. Ask the host to enable the PHP "gd" extension with WebP support.'], 422);
        }

        @set_time_limit(120);

        $offset = (int) $data['offset'];
        $limit = (int) ($data['limit'] ?? 12);

        // The list is the same on every call (the originals stay in place), so "offset" is a safe place to continue from.
        $pictures = WebpMigration::pictures();
        $batch = array_slice($pictures, $offset, $limit);

        $result = WebpMigration::convert($batch, self::MAX_SIDE);
        $rows = WebpMigration::updateDatabase($result['map']);

        $next = $offset + count($batch);
        $finished = $next >= count($pictures);

        if ($finished || $rows > 0) {
            try {
                Cache::flush();
            } catch (\Throwable) {
                // a cache that cannot be emptied must not stop the conversion
            }
        }

        return response()->json([
            'total' => count($pictures),
            'next' => $next,
            'done' => $finished,
            'converted' => $result['converted'],
            'saved' => $result['saved'],
            'rows' => $rows,
            'failed' => $result['failed'],
        ]);
    }
}
