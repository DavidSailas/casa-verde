<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Saves an uploaded picture in public/images. Photos and icons (JPG, PNG, GIF, WebP) are shrunk to at most
 * MAX_SIDE pixels on the long side and saved as WebP, so a 6 MB phone photo does not slow the site down.
 * SVG (already a vector) and animated GIF files are kept exactly as uploaded, and so is any picture the
 * server cannot convert (that is written to the log, see "php artisan images:webp --check").
 *
 * The file name keeps the convention used everywhere else on the site: 20261115_093000_<uuid>_<name>.<ext>
 */
class ImageUploader
{
    public const MAX_SIDE = 2000;

    public const QUALITY = 82;

    private const CONVERTIBLE = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * @param  int|null  $maxSide  longest side to keep (default MAX_SIDE); menu icons and dish photos use smaller limits
     * @return string the stored path, for example "images/20261115_093000_..._villa.webp"
     */
    public static function store(UploadedFile $file, ?int $maxSide = null): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'photo';
        $base = now()->format('Ymd_His').'_'.Str::uuid().'_'.$name;

        $converted = self::convert($file, $base, $maxSide ?? self::MAX_SIDE);

        if ($converted !== null) {
            return $converted;
        }

        $mime = (string) $file->getMimeType();

        if (in_array($mime, self::CONVERTIBLE, true) && ($mime !== 'image/gif' || ! self::isAnimatedGif((string) $file->getRealPath()))) {
            Log::warning('Picture saved as uploaded, not as WebP: '.$file->getClientOriginalName().' ('.$mime.'). Run "php artisan images:webp --check" to see why.');
        }

        $original = strtolower($file->getClientOriginalExtension());
        $extension = $original === 'svg' ? 'svg' : ($file->guessExtension() ?: ($original ?: 'jpg'));
        $filename = $base.'.'.$extension;

        $file->move(public_path('images'), $filename);

        return 'images/'.$filename;
    }

    /**
     * Whether this server can turn pictures into WebP (PHP GD with WebP, or Imagick with WebP).
     */
    public static function canConvert(): bool
    {
        return self::canGd() || self::canImagick();
    }

    public static function canGd(): bool
    {
        return extension_loaded('gd') && function_exists('imagewebp');
    }

    public static function canImagick(): bool
    {
        return extension_loaded('imagick') && class_exists(\Imagick::class) && \Imagick::queryFormats('WEBP') !== [];
    }

    /**
     * A GIF with more than one frame. GD cannot write an animated WebP, so these stay as GIF.
     */
    public static function isAnimatedGif(string $path): bool
    {
        $handle = $path !== '' ? @fopen($path, 'rb') : false;

        if ($handle === false) {
            return false;
        }

        $frames = 0;

        while (! feof($handle) && $frames < 2) {
            $chunk = fread($handle, 102400);

            if ($chunk === false) {
                break;
            }

            $frames += (int) preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $chunk);
        }

        fclose($handle);

        return $frames > 1;
    }

    private static function convert(UploadedFile $file, string $base, int $maxSide): ?string
    {
        $mime = (string) $file->getMimeType();
        $path = (string) $file->getRealPath();

        if ($path === '' || ! in_array($mime, self::CONVERTIBLE, true)) {
            return null;
        }

        if ($mime === 'image/gif' && self::isAnimatedGif($path)) {
            return null;
        }

        $filename = $base.'.webp';

        return self::encode($path, $mime, public_path('images/'.$filename), $maxSide) ? 'images/'.$filename : null;
    }

    /**
     * Writes a WebP copy of the picture at $path to $target, shrunk to at most $maxSide pixels on the long side.
     * Tries PHP GD first, then Imagick. Returns false (and leaves no half-written file) when it cannot.
     */
    public static function encode(string $path, string $mime, string $target, int $maxSide): bool
    {
        if (self::canGd() && self::encodeWithGd($path, $mime, $target, $maxSide)) {
            return true;
        }

        return self::canImagick() && self::encodeWithImagick($path, $target, $maxSide);
    }

    private static function encodeWithGd(string $path, string $mime, string $target, int $maxSide): bool
    {
        $size = @getimagesize($path);

        if ($size === false) {
            return false;
        }

        // A decoded picture needs about 4 bytes per pixel, and the shrunk copy exists at the same time.
        $ratio = min(1, $maxSide / max($size[0], $size[1]));
        $needed = (int) ((($size[0] * $size[1]) + ($size[0] * $size[1] * $ratio * $ratio)) * 4 * 1.3) + 8 * 1024 * 1024;
        $previousLimit = self::makeRoomFor($needed);

        if ($previousLimit === false) {
            return false; // not enough memory
        }

        try {
            $source = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                'image/gif' => @imagecreatefromgif($path),
                default => @imagecreatefromwebp($path),
            };

            if ($source === false) {
                return false;
            }

            // WebP cannot be written from a palette picture (an 8-bit PNG or a GIF).
            if (! imageistruecolor($source) && ! imagepalettetotruecolor($source)) {
                return false;
            }

            if ($mime === 'image/jpeg') {
                $source = self::turnUpright($source, $path);
            }

            $source = self::shrink($source, $maxSide);

            imagealphablending($source, false);
            imagesavealpha($source, true);

            if (! @imagewebp($source, $target, self::QUALITY)) {
                @unlink($target);

                return false;
            }

            return true;
        } finally {
            unset($source);

            if ($previousLimit !== null) {
                ini_set('memory_limit', $previousLimit);
            }
        }
    }

    private static function encodeWithImagick(string $path, string $target, int $maxSide): bool
    {
        try {
            $image = new \Imagick($path.'[0]');

            if (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }

            if (max($image->getImageWidth(), $image->getImageHeight()) > $maxSide) {
                $image->scaleImage($maxSide, $maxSide, true);
            }

            $image->setImageFormat('webp');
            $image->setImageCompressionQuality(self::QUALITY);
            $image->stripImage();

            $written = $image->writeImage($target);
            $image->clear();

            return (bool) $written;
        } catch (\Throwable) {
            @unlink($target);

            return false;
        }
    }

    /**
     * Makes sure about $bytes can be allocated, raising PHP's memory limit for this conversion if needed.
     *
     * @return string|false|null the limit to put back afterwards (null when it was not changed), or false when
     *                           there is not enough memory and it cannot be raised
     */
    private static function makeRoomFor(int $bytes): string|false|null
    {
        $current = (string) ini_get('memory_limit');
        $limit = self::bytes($current);

        if ($limit < 0 || $limit - memory_get_usage(true) >= $bytes) {
            return null;
        }

        if (@ini_set('memory_limit', (string) ($limit + $bytes)) === false) {
            return false;
        }

        return $current;
    }

    private static function bytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /**
     * Phone photos are often stored sideways with a note saying how to turn them; apply that note.
     */
    private static function turnUpright(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);

        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        return imagerotate($image, $angle, 0) ?: $image;
    }

    private static function shrink(\GdImage $image, int $maxSide): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxSide) {
            return $image;
        }

        $ratio = $maxSide / $longest;
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }
}
