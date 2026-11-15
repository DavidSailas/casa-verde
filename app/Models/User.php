<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'must_change_password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /** The roles staff can have, and what each may open in the admin. */
    public const ROLES = [
        'owner' => 'Owner',
        'manager' => 'Manager',
        'editor' => 'Editor',
    ];

    public const ROLE_HELP = [
        'owner' => 'Everything, including staff accounts and the history of changes.',
        'manager' => 'Site content, media, bookings, availability, reviews, languages and the AI assistant. No staff accounts.',
        'editor' => 'Site content, media, gallery photos and languages. Cannot see bookings or guest details.',
    ];

    /** The admin areas each role can open. '*' means all of them. */
    public const ACCESS = [
        'owner' => ['*'],
        'manager' => ['content', 'media', 'gallery', 'languages', 'ai-brain', 'bookings', 'availability', 'reviews'],
        'editor' => ['content', 'media', 'gallery', 'languages'],
    ];

    /** Accounts created before roles existed have no role and keep full access, as owners. */
    public function roleKey(): string
    {
        return array_key_exists((string) $this->role, self::ROLES) ? (string) $this->role : 'owner';
    }

    public function isOwner(): bool
    {
        return $this->is_admin && $this->roleKey() === 'owner';
    }

    /** Whether this person may open an area of the admin (content, bookings, availability, staff...). */
    public function canAccess(string $area): bool
    {
        if (! $this->is_admin) {
            return false;
        }

        $allowed = self::ACCESS[$this->roleKey()] ?? [];

        return in_array('*', $allowed, true) || in_array($area, $allowed, true);
    }
}
