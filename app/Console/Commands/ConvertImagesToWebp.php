<?php

namespace App\Console\Commands;

use App\Support\ImageUploader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:webp
        {--check : only show whether this server can create WebP pictures}
        {--dry-run : show what would happen without changing anything}
        {--code : also change fixed picture names in views, CSS, JS and PHP files}
        {--delete-originals : delete the old JPG/PNG/GIF files afterwards (needs --code)}
        {--max-side=2400 : longest side, in pixels, to keep}
        {--skip=* : leave out pictures whose path contains this text (can be repeated)}';

    protected $description = 'Convert the JPG, PNG and GIF pictures in public/images to WebP and point the site at them';

    /** Pictures that must stay as they are: browsers and e-mail programs ask for these by name or format. */
    private const ALWAYS_SKIP = ['favicon', 'apple-touch', 'email'];

    private const TABLES_TO_IGNORE = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
        'password_reset_tokens', 'personal_access_tokens', 'sqlite_sequence',
    ];

    public function handle(): int
    {
        if ($this->option('check')) {
            return $this->check();
        }

        if (! ImageUploader::canConvert()) {
            $this->error('This server cannot create WebP pictures. Run "php artisan images:webp --check" for details.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $maxSide = max(200, (int) $this->option('max-side'));
        $skip = array_merge(self::ALWAYS_SKIP, array_map('strval', (array) $this->option('skip')));

        $this->info($dry ? 'Dry run: nothing will be changed.' : 'Converting pictures in public/images ...');

        $map = [];
        $converted = 0;
        $savedBytes = 0;
        $failed = [];

        foreach (File::allFiles(public_path('images')) as $file) {
            $extension = strtolower($file->getExtension());

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                continue;
            }

            $relative = 'images/'.str_replace('\\', '/', $file->getRelativePathname());

            if ($this->skipped($relative, $skip)) {
                continue;
            }

            $source = $file->getRealPath();

            if ($extension === 'gif' && ImageUploader::isAnimatedGif($source)) {
                $this->line("  kept (animated GIF): {$relative}");

                continue;
            }

            $webp = (string) preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $relative);
            $target = public_path($webp);

            if (is_file($target)) {
                $this->line("  left alone (a WebP with this name already exists): {$relative}");

                continue;
            }

            if ($dry) {
                $map[$relative] = $webp;
                $converted++;

                continue;
            }

            $mime = match ($extension) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };

            if (! ImageUploader::encode($source, $mime, $target, $maxSide)) {
                $failed[] = $relative;

                continue;
            }

            if (filesize($target) >= filesize($source)) {
                @unlink($target);
                $this->line("  kept (WebP would not be smaller): {$relative}");

                continue;
            }

            $savedBytes += filesize($source) - filesize($target);
            $map[$relative] = $webp;
            $converted++;
        }

        $rows = $this->updateDatabase($map, $dry);
        $files = $this->option('code') ? $this->updateCode($map, $dry) : 0;

        if ($this->option('delete-originals')) {
            if (! $this->option('code')) {
                $this->warn('Originals were NOT deleted: use --delete-originals together with --code, so no page still asks for them.');
            } elseif (! $dry) {
                foreach (array_keys($map) as $old) {
                    @unlink(public_path($old));
                }
                $this->line('  deleted '.count($map).' original file(s)');
            }
        }

        if (! $dry && $map !== []) {
            $this->call('cache:clear');
            $this->call('view:clear');
        }

        $this->newLine();
        $this->info(($dry ? 'Would convert ' : 'Converted ')."{$converted} picture(s)".($dry ? '' : ', saving '.$this->size($savedBytes)).'.');
        $this->line("Database rows ".($dry ? 'that would change' : 'updated').": {$rows}");

        if ($this->option('code')) {
            $this->line('Code files '.($dry ? 'that would change' : 'changed').": {$files}");
        } else {
            $this->line('Fixed picture names inside views, CSS and PHP files were not touched (the old files are still there, so they keep working). Add --code to change those too.');
        }

        foreach ($failed as $path) {
            $this->warn("  could not convert: {$path}");
        }

        return self::SUCCESS;
    }

    private function check(): int
    {
        $this->line('PHP '.PHP_VERSION.', memory_limit '.ini_get('memory_limit'));
        $this->line('GD extension: '.(extension_loaded('gd') ? 'yes' : 'no').', WebP support in GD: '.(function_exists('imagewebp') ? 'yes' : 'no'));
        $this->line('Imagick extension: '.(extension_loaded('imagick') ? 'yes' : 'no').', WebP support in Imagick: '.(ImageUploader::canImagick() ? 'yes' : 'no'));

        $left = 0;

        foreach (File::allFiles(public_path('images')) as $file) {
            if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif'], true)) {
                $left++;
            }
        }

        $this->line("JPG/PNG/GIF pictures still in public/images: {$left}");

        if (ImageUploader::canConvert()) {
            $this->info('OK: new uploads in the admin are converted to WebP automatically.');

            return self::SUCCESS;
        }

        $this->error('This server cannot create WebP pictures, so uploads are saved as they are. Ask the host to enable the PHP "gd" extension with WebP support (or "imagick").');

        return self::FAILURE;
    }

    /**
     * @param  array<string, string>  $map  old path => new path
     */
    private function updateDatabase(array $map, bool $dry): int
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

    /**
     * @param  array<string, string>  $map  old path => new path
     */
    private function updateCode(array $map, bool $dry): int
    {
        if ($map === []) {
            return 0;
        }

        $changed = 0;

        foreach (['resources/views', 'resources/css', 'resources/js', 'app', 'config', 'routes', 'database/seeders'] as $folder) {
            $root = base_path($folder);

            if (! is_dir($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                if (! in_array(strtolower($file->getExtension()), ['php', 'css', 'js'], true) || $file->getFilename() === 'ConvertImagesToWebp.php') {
                    continue;
                }

                $before = (string) file_get_contents($file->getRealPath());
                $after = strtr($before, $map);

                if ($after === $before) {
                    continue;
                }

                $changed++;

                if (! $dry) {
                    file_put_contents($file->getRealPath(), $after);
                }
            }
        }

        return $changed;
    }

    /**
     * @param  list<string>  $skip
     */
    private function skipped(string $relative, array $skip): bool
    {
        foreach ($skip as $needle) {
            if ($needle !== '' && str_contains(strtolower($relative), strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function size(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
