<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaTrash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The "Clean up old pictures" card on the Media library page and the "Move to trash" button on each picture.
 */
class MediaTrashController extends Controller
{
    public function status(): JsonResponse
    {
        $candidates = MediaTrash::candidates();
        $trashed = MediaTrash::trashed();

        $group = fn (string $reason) => [
            'count' => $candidates->where('reason', $reason)->count(),
            'bytes' => (int) $candidates->where('reason', $reason)->sum('bytes'),
        ];

        return response()->json([
            'webp' => $group('webp'),
            'older' => $group('older'),
            'trash' => ['count' => $trashed->count(), 'bytes' => (int) $trashed->sum('bytes')],
        ]);
    }

    /**
     * Moves the given pictures to the trash, or (without "paths") every picture that is safe to remove.
     */
    public function move(Request $request): JsonResponse
    {
        $data = $request->validate([
            'paths' => ['nullable', 'array', 'max:2000'],
            'paths.*' => ['string', 'max:300'],
            'reasons' => ['nullable', 'array'],
            'reasons.*' => ['in:webp,older'],
        ]);

        if (! empty($data['paths'])) {
            $paths = $data['paths'];
        } else {
            $paths = MediaTrash::candidates()
                ->whereIn('reason', $data['reasons'] ?? ['webp', 'older'])
                ->pluck('path')
                ->all();
        }

        return response()->json(MediaTrash::move($paths));
    }

    public function restore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'paths' => ['nullable', 'array', 'max:2000'],
            'paths.*' => ['string', 'max:300'],
        ]);

        return response()->json(MediaTrash::restore($data['paths'] ?? null));
    }

    public function empty(): JsonResponse
    {
        return response()->json(MediaTrash::empty());
    }
}
