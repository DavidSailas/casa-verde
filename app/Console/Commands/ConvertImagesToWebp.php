<?php

namespace App\Console\Commands;

use App\Support\ImageUploader;
use App\Support\WebpMigration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:webp
        {--check : only show whether this server can create WebP pictures}
        {--dry-run : show what would happen without changing anything}
        {--code : also change fixed picture names in views, CSS, JS and PHP files}
        {--delete-originals : delete the old JPG/PNG/GIF files afterwards (needs --code)}
        {--only-smaller : leave a picture as it is when its WebP would not be smaller}
        {--max-side=2400 : longest side, in pixels, to keep}
        {--skip=* : leave out pictures whose path contains this text (can be repeated)}';

    protected $description = 'Convert the JPG, PNG and GIF pictures in public/images to WebP and point the site at them';

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
        $pictures = WebpMigration::pictures(array_map('strval', (array) $this->option('skip')));

        $this->info($dry ? 'Dry run: nothing will be changed.' : 'Converting pictures in public/images ...');

        $result = WebpMigration::convert($pictures, $maxSide, $dry, fn (string $message) => $this->line('  '.$message), (bool) $this->option('only-smaller'));
        $map = $result['map'];

        $rows = WebpMigration::updateDatabase($map, $dry);
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
            $this->callSilent('cache:clear');
            $this->callSilent('view:clear');
        }

        $this->newLine();
        $this->info(($dry ? 'Would convert ' : 'Converted ')."{$result['converted']} picture(s)".($dry ? '' : ', saving '.$this->size($result['saved'])).'.');
        $this->line('Database rows '.($dry ? 'that would change' : 'updated').": {$rows}");

        if ($this->option('code')) {
            $this->line('Code files '.($dry ? 'that would change' : 'changed').": {$files}");
        } else {
            $this->line('Fixed picture names inside views, CSS and PHP files were not touched (the old files are still there, so they keep working). Add --code to change those too.');
        }

        foreach ($result['failed'] as $path) {
            $this->warn("  could not convert: {$path}");
        }

        return self::SUCCESS;
    }

    private function check(): int
    {
        $this->line('PHP '.PHP_VERSION.', memory_limit '.ini_get('memory_limit'));
        $this->line('GD extension: '.(extension_loaded('gd') ? 'yes' : 'no').', WebP support in GD: '.(function_exists('imagewebp') ? 'yes' : 'no'));
        $this->line('Imagick extension: '.(extension_loaded('imagick') ? 'yes' : 'no').', WebP support in Imagick: '.(ImageUploader::canImagick() ? 'yes' : 'no'));
        $this->line('JPG/PNG/GIF pictures in public/images: '.count(WebpMigration::pictures()));

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

    private function size(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
