<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminRole extends Model
{
    protected $table = 'admin_roles';

    /**
     * @return BelongsToMany<AdminUser, $this>
     */
    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminUser::class,
            'admin_user_roles',
            'role_id',
            'admin_user_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany<AdminPermission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'admin_role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }
}
