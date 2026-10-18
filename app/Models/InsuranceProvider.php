<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'contact_info',
        'status',
        'api_environment',
        'api_base_url',
        'facility_code',
        'api_key',
        'api_secret',
        'auth_token',
        'auth_expires_at',
        'is_integrated',
        'settings',
        'tenant_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_integrated' => 'boolean',
        'settings' => 'array',
        'auth_expires_at' => 'datetime',
    ];

    public function claims()
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function isSandbox(): bool
    {
        return ($this->api_environment ?? 'sandbox') === 'sandbox';
    }
}
