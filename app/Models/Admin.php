<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * Table name (important because it's not plural)
     */
    protected $table = 'admin';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',

        'last_seen_at' => 'datetime',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // =========================
    // 🧠 ROLE HELPERS
    // =========================

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    // =========================
    // 📊 STATUS HELPERS
    // =========================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // =========================
    // 🔐 LOGIN TRACKING
    // =========================

    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now()
        ]);
    }
    // app/Models/Admin.php

// Update in app/Models/Admin.php
public function hasRole($role)
{
    // If they are a super_admin, they have permission for everything
    if ($this->role === 'super_admin') {
        return true;
    }

    return $this->role === $role;
}
}