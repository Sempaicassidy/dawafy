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
        'role_id',
        'pc_reg_number',
        'tenant_id',
        'status',
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
}
