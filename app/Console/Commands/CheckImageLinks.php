<?php

namespace App\Console\Commands;

use App\Support\WebpMigration;
use Illuminate\Console\Command;

class CheckImageLinks extends Command
{
    protected $signature = 'images:check {--fix : point a missing picture to the same picture in another format that exists}';

    protected $description = 'List the pictures the site asks for but that are missing on this server, and repair the links when a copy exists';

    public function handle(): int
    {
        $references = WebpMigration::references();
        $missing = array_filter($references, fn (int $count, string $path) => ! is_file(public_path($path)), ARRAY_FILTER_USE_BOTH);

        $this->line('The database asks for '.count($references).' different pictures.');

        if ($missing === []) {
            $this->info('All of them exist on this server.');

            return self::SUCCESS;
        }

        $fixable = [];
        $lost = [];

        foreach (array_keys($missing) as $path) {
            $other = WebpMigration::alternative($path);

            if ($other !== null) {
                $fixable[$path] = $other;
            } else {
                $lost[] = $path;
            }
        }

        $this->warn(count($missing).' picture(s) are missing on this server.');

        foreach ($fixable as $path => $other) {
            $this->line("  can be repaired: {$path}  ->  {$other}");
        }

        foreach ($lost as $path) {
            $this->error("  file not on this server: {$path}");
        }

        if ($fixable !== [] && $this->option('fix')) {
            $rows = WebpMigration::updateDatabase($fixable);
            $this->callSilent('cache:clear');
            $this->info("Repaired: {$rows} place(s) in the site content now point to a file that exists.");
        } elseif ($fixable !== []) {
            $this->line('Run "php artisan images:check --fix" to repair the ones marked "can be repaired".');
        }

        if ($lost !== []) {
            $this->line('The "file not on this server" pictures must be uploaded to this server (copy them into public/images, or upload them again in the admin).');
        }

        return $lost === [] ? self::SUCCESS : self::FAILURE;
    }
}
