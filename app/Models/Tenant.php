<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo_path',
        'enable_pharmacist',
        'enable_dda',
        'payment_status',
        'subscription_price',
        'license_expiry',
        'tin',
        'vat_reg',
        'parent_id',
        'status',
        'feature_ai_sales',
        'feature_whatsapp',
        'feature_wallet',
        'feature_data_transfer',
        'feature_erp',
        'feature_online_purchases',
        'feature_vat',
        'feature_backup',
        'feature_advanced_pharmacy',
    ];

    protected $casts = [
        'enable_pharmacist' => 'boolean',
        'enable_dda' => 'boolean',
        'subscription_price' => 'decimal:2',
        'license_expiry' => 'date',
        'feature_ai_sales' => 'boolean',
        'feature_whatsapp' => 'boolean',
        'feature_wallet' => 'boolean',
        'feature_data_transfer' => 'boolean',
        'feature_erp' => 'boolean',
        'feature_online_purchases' => 'boolean',
        'feature_vat' => 'boolean',
        'feature_backup' => 'boolean',
        'feature_advanced_pharmacy' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function parent()
    {
        return $this->belongsTo(Tenant::class, 'parent_id');
    }

    public function branches()
    {
        return $this->hasMany(Tenant::class, 'parent_id');
    }

    public function isExpired()
    {
        return $this->license_expiry && $this->license_expiry < now();
    }
}
