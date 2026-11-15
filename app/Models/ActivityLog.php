<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of the admin history: who did what, to what, and when.
 * Passwords and other typed-in values are never stored here.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'user_name', 'action', 'subject', 'details', 'ip'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Writes a line. A problem with the history must never stop the change itself, so errors are swallowed. */
    public static function record(string $action, ?string $subject = null, ?string $details = null, ?User $user = null): void
    {
        try {
            $user ??= auth()->user();

            static::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'action' => mb_substr($action, 0, 120),
                'subject' => $subject !== null ? mb_substr($subject, 0, 255) : null,
                'details' => $details !== null ? mb_substr($details, 0, 255) : null,
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** A plain-English line for an admin route name such as "admin.bookings.update". */
    public static function describe(string $routeName): string
    {
        $special = [
            'admin.reviews.sync-google' => 'Synced Google reviews',
            'admin.ai-brain.drafts' => 'Saved AI assistant drafts',
            'admin.ai-brain.connection.save' => 'Changed the AI connection',
            'admin.ai-brain.connection.remove' => 'Removed the AI connection',
            'admin.gallery.move' => 'Moved a gallery photo',
            'admin.content.reorder' => 'Changed the order of site content',
            'admin.account.update' => 'Updated their own account',
        ];

        if (isset($special[$routeName])) {
            return $special[$routeName];
        }

        $parts = explode('.', $routeName); // admin, bookings, update
        array_shift($parts);

        $areas = [
            'content' => 'site content',
            'media' => 'the media library',
            'gallery' => 'a gallery photo',
            'ai-brain' => 'the AI assistant',
            'languages' => 'the website languages',
            'bookings' => 'a booking',
            'reviews' => 'a review',
            'staff' => 'a staff account',
            'account' => 'their account',
            'availability' => [
                'blocks' => 'closed dates',
                'seasons' => 'a seasonal rate',
                'units' => 'the number of villas',
            ],
        ];

        $area = $areas[$parts[0] ?? ''] ?? 'the admin';

        if (is_array($area)) {
            $area = $area[$parts[1] ?? ''] ?? 'availability';
        }

        $action = end($parts) ?: '';
        $verb = match (true) {
            str_contains($action, 'store') => 'Added',
            str_contains($action, 'destroy'), str_contains($action, 'remove') => 'Deleted',
            default => 'Changed',
        };

        return "{$verb} {$area}";
    }
}
