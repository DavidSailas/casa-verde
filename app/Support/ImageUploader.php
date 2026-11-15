<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Saves an uploaded picture in public/images. Photos and icons (JPG, PNG, WebP) are shrunk to at most
 * MAX_SIDE pixels on the long side and saved as WebP, so a 6 MB phone photo does not slow the site down.
 * SVG and GIF files are kept exactly as uploaded, and so is any picture the server cannot convert.
 *
 * The file name keeps the convention used everywhere else on the site: 20261115_093000_<uuid>_<name>.<ext>
 */
class ImageUploader
{
    public const MAX_SIDE = 2000;

    public const QUALITY = 82;

    private const CONVERTIBLE = ['image/jpeg', 'image/png', 'image/webp'];

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

        $original = strtolower($file->getClientOriginalExtension());
        $extension = $original === 'svg' ? 'svg' : ($file->guessExtension() ?: ($original ?: 'jpg'));
        $filename = $base.'.'.$extension;

        $file->move(public_path('images'), $filename);

        return 'images/'.$filename;
    }

    /**
     * Whether this server can turn pictures into WebP.
     */
    public static function canConvert(): bool
    {
        return extension_loaded('gd') && function_exists('imagewebp');
    }

    private static function convert(UploadedFile $file, string $base, int $maxSide): ?string
    {
        $mime = $file->getMimeType();
        $path = $file->getRealPath();

        if (! self::canConvert() || ! $path || ! in_array($mime, self::CONVERTIBLE, true)) {
            return null;
        }

        $size = @getimagesize($path);

        if ($size === false) {
            return null;
        }

        // A decoded picture needs about 4 bytes per pixel, and the shrunk copy exists at the same time.
        $ratio = min(1, $maxSide / max($size[0], $size[1]));
        $needed = (int) ((($size[0] * $size[1]) + ($size[0] * $size[1] * $ratio * $ratio)) * 4 * 1.3) + 8 * 1024 * 1024;
        $previousLimit = self::makeRoomFor($needed);

        if ($previousLimit === false) {
            return null; // not enough memory: the original is stored as it is
        }

        try {
            $source = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                default => @imagecreatefromwebp($path),
            };

            if ($source === false) {
                return null;
            }

            // WebP cannot be written from a palette picture (an 8-bit PNG).
            if (! imageistruecolor($source) && ! imagepalettetotruecolor($source)) {
                return null;
            }

            if ($mime === 'image/jpeg') {
                $source = self::turnUpright($source, $path);
            }

            $source = self::shrink($source, $maxSide);

            imagealphablending($source, false);
            imagesavealpha($source, true);

            $filename = $base.'.webp';
            $target = public_path('images/'.$filename);

            if (! @imagewebp($source, $target, self::QUALITY)) {
                @unlink($target);

                return null;
            }

            return 'images/'.$filename;
        } finally {
            unset($source);

            if ($previousLimit !== null) {
                ini_set('memory_limit', $previousLimit);
            }
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
