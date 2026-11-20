<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * A safety net for removing pictures from the Media library. Nothing is deleted straight away: a picture is moved
 * from public/images to storage/app/media-trash (outside the public folder), keeps its folder structure, and can
 * be put back with one click until the trash is emptied.
 */
class MediaTrash
{
    private const FOLDER = 'media-trash';

    public static function root(): string
    {
        return storage_path('app/'.self::FOLDER);
    }

    /**
     * Pictures that are safe to remove. A picture only counts when nothing uses it (no admin entry, no page design,
     * no starting design) and it is one of:
     *  - "webp":  an old JPG/PNG/GIF photo whose WebP copy exists, or
     *  - "older": an older upload that a newer upload of the same picture replaced.
     * Icons and symbols are never listed as "webp": the code can pick those by name, so they are only removed by hand.
     *
     * @return Collection<int, array{path: string, name: string, bytes: int, reason: string}>
     */
    public static function candidates(?Collection $library = null): Collection
    {
        $library ??= MediaLibrary::all();

        return $library
            ->filter(fn (array $file) => $file['status'] === 'unused')
            ->map(function (array $file): ?array {
                $reason = null;

                if (! empty($file['newer'])) {
                    $reason = 'older';
                } elseif (
                    $file['type'] === 'photo'
                    && in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif'], true)
                    && is_file(public_path(WebpMigration::webpPath($file['path'])))
                ) {
                    $reason = 'webp';
                }

                return $reason === null ? null : [
                    'path' => $file['path'],
                    'name' => $file['name'],
                    'bytes' => (int) $file['bytes'],
                    'reason' => $reason,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Moves pictures to the trash. Every path is checked again here: it must be inside public/images and nothing may
     * use it, so a stale page or a forged request can never remove a picture that is on the site.
     *
     * @param  list<string>  $paths
     * @return array{moved: int, bytes: int, skipped: list<string>}
     */
    public static function move(array $paths): array
    {
        $library = MediaLibrary::all()->keyBy('path');
        $moved = 0;
        $bytes = 0;
        $skipped = [];

        foreach (array_unique($paths) as $path) {
            $path = self::cleanPath((string) $path);
            $file = $path !== null ? $library->get($path) : null;

            if ($file === null || $file['status'] !== 'unused') {
                $skipped[] = $path ?? '(invalid path)';

                continue;
            }

            $from = public_path($path);
            $to = self::root().'/'.$path;

            File::ensureDirectoryExists(dirname($to));

            if (@rename($from, $to) || (@copy($from, $to) && @unlink($from))) {
                $moved++;
                $bytes += (int) $file['bytes'];
            } else {
                $skipped[] = $path;
            }
        }

        return ['moved' => $moved, 'bytes' => $bytes, 'skipped' => $skipped];
    }

    /**
     * @return Collection<int, array{path: string, bytes: int}>
     */
    public static function trashed(): Collection
    {
        if (! is_dir(self::root())) {
            return collect();
        }

        return collect(File::allFiles(self::root()))
            ->map(fn ($file) => [
                'path' => str_replace('\\', '/', $file->getRelativePathname()),
                'bytes' => (int) $file->getSize(),
            ])
            ->values();
    }

    /**
     * Puts pictures back where they were. Without a list, everything in the trash is restored. A picture is not
     * restored over a file that now has the same name.
     *
     * @param  list<string>|null  $paths
     * @return array{restored: int, skipped: list<string>}
     */
    public static function restore(?array $paths = null): array
    {
        $restored = 0;
        $skipped = [];
        $wanted = $paths === null ? null : array_flip(array_map('strval', $paths));

        foreach (self::trashed() as $item) {
            if ($wanted !== null && ! isset($wanted[$item['path']])) {
                continue;
            }

            $path = self::cleanPath($item['path']);

            if ($path === null || is_file(public_path($path))) {
                $skipped[] = $item['path'];

                continue;
            }

            File::ensureDirectoryExists(dirname(public_path($path)));

            if (@rename(self::root().'/'.$path, public_path($path)) || (@copy(self::root().'/'.$path, public_path($path)) && @unlink(self::root().'/'.$path))) {
                $restored++;
            } else {
                $skipped[] = $item['path'];
            }
        }

        return ['restored' => $restored, 'skipped' => $skipped];
    }

    /**
     * Deletes everything in the trash for good.
     *
     * @return array{deleted: int, bytes: int}
     */
    public static function empty(): array
    {
        $items = self::trashed();

        if (is_dir(self::root())) {
            File::deleteDirectory(self::root());
        }

        return ['deleted' => $items->count(), 'bytes' => (int) $items->sum('bytes')];
    }

    /**
     * Only plain paths inside images/ are accepted.
     */
    private static function cleanPath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || strlen($path) > 300 || ! str_starts_with($path, 'images/') || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return preg_match('#^images/[A-Za-z0-9 _.\-/()\[\]]+$#', $path) === 1 ? $path : null;
    }
}
