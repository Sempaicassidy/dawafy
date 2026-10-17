<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'whatsapp_api_key',
        'whatsapp_phone_number',
        'whatsapp_business_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
