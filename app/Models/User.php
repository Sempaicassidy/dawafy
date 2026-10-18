<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'phone',
        'role_id',
        'pc_reg_number',
        'tenant_id',
        'status',
        'secondary_roles',
        'session_id',
        'last_activity',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity' => 'datetime',
        'secondary_roles' => 'array',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['full_name'] ?? $this->attributes['name'] ?? $this->attributes['username'] ?? 'User';
    }

    public function getAuthPassword()
    {
        return $this->attributes['password'] ?? $this->attributes['password_hash'] ?? null;
    }

    public function getPasswordAttribute()
    {
        return $this->attributes['password'] ?? $this->attributes['password_hash'] ?? null;
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if (!empty($user->password) && empty($user->password_hash)) {
                $user->password_hash = $user->password;
            } elseif (!empty($user->password_hash) && empty($user->password)) {
                $user->password = $user->password_hash;
            }
        });
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function approvedDDA()
    {
        return $this->hasMany(DDARegister::class, 'approved_by_user_id');
    }

    public function getAllRolesAttribute(): array
    {
        $primary = $this->role?->name;
        if ($primary === 'Admin') {
            $primary = 'Manager';
        }

        $secondary = is_array($this->secondary_roles) ? $this->secondary_roles : json_decode($this->secondary_roles ?? '[]', true) ?? [];
        $mappedSecondary = array_map(fn($r) => ($r === 'Admin') ? 'Manager' : $r, $secondary);

        $all = array_filter(array_unique(array_merge([$primary], $mappedSecondary)));
        return empty($all) ? ['Staff'] : array_values($all);
    }

    public function hasRole(string $roleName): bool
    {
        if ($roleName === 'Admin') {
            $roleName = 'Manager';
        }
        return in_array($roleName, $this->all_roles);
    }

    public function isManager(): bool
    {
        return $this->hasRole('Manager') || $this->hasRole('Admin') || $this->hasRole('Super Admin');
    }

    public function isPharmacist(): bool
    {
        return $this->hasRole('Pharmacist');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('Cashier');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }
}
