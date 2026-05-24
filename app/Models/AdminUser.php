<?php

namespace App\Models;

use Database\Factories\AdminUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class AdminUser extends Authenticatable
{
    /** @use HasFactory<AdminUserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'admin_users';

    public const SUPER_ADMIN_SLUG = 'super-admin';

    /** @var Collection<int, string>|null */
    protected ?Collection $permissionSlugCache = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar_path',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsToMany<AdminRole, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'admin_user_roles',
            'admin_user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * In-app notifications targeted at this admin (excludes global admin rows with null recipient_id).
     *
     * @return HasMany<Notification, $this>
     */
    public function storeNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id')
            ->where('recipient_type', 'admin');
    }

    /**
     * Permissions granted through assigned roles (deduplicated).
     * For repetitive checks use {@see hasPermission()}; eager-load with `$admin->load('roles.permissions')` to limit queries.
     *
     * @return Collection<int, AdminPermission>
     */
    public function permissionsThroughRoles(): Collection
    {
        $this->loadMissing('roles.permissions');

        return $this->roles->pluck('permissions')->flatten()->unique('id')->values();
    }

    /**
     * Cached permission slugs for the current request (avoids N+1 in menus).
     *
     * @return Collection<int, string>
     */
    protected function permissionSlugSet(): Collection
    {
        if ($this->permissionSlugCache instanceof Collection) {
            return $this->permissionSlugCache;
        }

        $this->loadMissing('roles.permissions');

        return $this->permissionSlugCache = $this->permissionsThroughRoles()
            ->pluck('slug')
            ->unique()
            ->values();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('admin_roles.slug', $slug)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::SUPER_ADMIN_SLUG);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionSlugSet()->contains($permissionSlug);
    }
}
