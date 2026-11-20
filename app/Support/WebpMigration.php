<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the JPG, PNG and GIF pictures that are already in public/images into WebP, and points the database at
 * the new files. Used by "php artisan images:webp" and by the button on the admin Media library page.
 * The original files are never touched here, so a page that still asks for one keeps working.
 */
class WebpMigration
{
    /** Pictures that must stay as they are: browsers and e-mail programs ask for these by name or format. */
    public const SKIP = ['favicon', 'apple-touch', 'email'];

    private const TABLES_TO_IGNORE = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
        'password_reset_tokens', 'personal_access_tokens', 'sqlite_sequence',
    ];

    /**
     * Every JPG, PNG and GIF in public/images that may be converted, in a fixed order.
     *
     * @param  list<string>  $extraSkip  leave out paths that contain any of these texts
     * @return list<array{relative: string, path: string, ext: string}>
     */
    public static function pictures(array $extraSkip = []): array
    {
        $skip = array_merge(self::SKIP, $extraSkip);
        $found = [];

        if (! is_dir(public_path('images'))) {
            return [];
        }

        foreach (File::allFiles(public_path('images')) as $file) {
            $ext = strtolower($file->getExtension());

            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                continue;
            }

            $relative = 'images/'.str_replace('\\', '/', $file->getRelativePathname());
            $lower = strtolower($relative);

            foreach ($skip as $needle) {
                if ($needle !== '' && str_contains($lower, strtolower($needle))) {
                    continue 2;
                }
            }

            $found[] = ['relative' => $relative, 'path' => (string) $file->getRealPath(), 'ext' => $ext];
        }

        usort($found, fn (array $a, array $b) => strcmp($a['relative'], $b['relative']));

        return $found;
    }

    public static function webpPath(string $relative): string
    {
        return (string) preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $relative);
    }

    /**
     * Converts the given pictures. A picture is left alone when a WebP with the same name exists, when it is an
     * animated GIF, or (only when $onlyIfSmaller is true) when the WebP would not be smaller. By default every
     * picture becomes WebP, so the whole site uses one format.
     *
     * @param  list<array{relative: string, path: string, ext: string}>  $batch
     * @param  (callable(string): void)|null  $note
     * @return array{map: array<string, string>, converted: int, saved: int, failed: list<string>}
     */
    public static function convert(array $batch, int $maxSide, bool $dry = false, ?callable $note = null, bool $onlyIfSmaller = false): array
    {
        $say = function (string $message) use ($note): void {
            if ($note !== null) {
                $note($message);
            }
        };

        $map = [];
        $converted = 0;
        $saved = 0;
        $failed = [];

        foreach ($batch as $picture) {
            $relative = $picture['relative'];
            $source = $picture['path'];

            if ($picture['ext'] === 'gif' && ImageUploader::isAnimatedGif($source)) {
                $say("kept (animated GIF): {$relative}");

                continue;
            }

            $webp = self::webpPath($relative);
            $target = public_path($webp);

            if (is_file($target)) {
                $say("left alone (a WebP with this name already exists): {$relative}");

                continue;
            }

            if ($dry) {
                $map[$relative] = $webp;
                $converted++;

                continue;
            }

            $mime = match ($picture['ext']) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };

            if (! ImageUploader::encode($source, $mime, $target, $maxSide)) {
                $failed[] = $relative;

                continue;
            }

            if ($onlyIfSmaller && filesize($target) >= filesize($source)) {
                @unlink($target);
                $say("kept (WebP would not be smaller): {$relative}");

                continue;
            }

            $saved += max(0, filesize($source) - filesize($target));
            $map[$relative] = $webp;
            $converted++;
        }

        return ['map' => $map, 'converted' => $converted, 'saved' => $saved, 'failed' => $failed];
    }

    /**
     * Replaces the old picture names with the new ones in every text column of every table that has an "id".
     * Returns the number of rows that changed (or would change, on a dry run).
     *
     * @param  array<string, string>  $map  old path => new path
     */
    public static function updateDatabase(array $map, bool $dry = false): int
    {
        if ($map === []) {
            return 0;
        }

        // JSON columns store slashes escaped ("images\/a.jpg"), so both spellings are replaced.
        $pairs = [];

        foreach ($map as $old => $new) {
            $pairs[$old] = $new;
            $pairs[str_replace('/', '\\/', $old)] = str_replace('/', '\\/', $new);
        }

        $updated = 0;

        DB::transaction(function () use ($pairs, $dry, &$updated): void {
            foreach (Schema::getTables() as $table) {
                $name = $table['name'];

                if (in_array($name, self::TABLES_TO_IGNORE, true) || ! Schema::hasColumn($name, 'id')) {
                    continue;
                }

                $columns = [];

                foreach (Schema::getColumns($name) as $column) {
                    if ($column['name'] !== 'id' && preg_match('/char|text|json|string/i', (string) $column['type_name'])) {
                        $columns[] = $column['name'];
                    }
                }

                if ($columns === []) {
                    continue;
                }

                DB::table($name)->orderBy('id')->chunkById(200, function ($rows) use ($name, $columns, $pairs, $dry, &$updated): void {
                    foreach ($rows as $row) {
                        $changes = [];

                        foreach ($columns as $column) {
                            $value = $row->{$column} ?? null;

                            if (! is_string($value) || ! str_contains($value, 'images')) {
                                continue;
                            }

                            $new = strtr($value, $pairs);

                            if ($new !== $value) {
                                $changes[$column] = $new;
                            }
                        }

                        if ($changes === []) {
                            continue;
                        }

                        $updated++;

                        if (! $dry) {
                            DB::table($name)->where('id', $row->id)->update($changes);
                        }
                    }
                });
            }
        });

        return $updated;
    }
}
