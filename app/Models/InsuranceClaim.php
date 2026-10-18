<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'insurance_provider_id',
        'provider_id',
        'claim_number',
        'member_number',
        'member_name',
        'scheme_name',
        'authorization_code',
        'referral_number',
        'amount',
        'total_amount',
        'status',
        'diagnosis',
        'notes',
        'prescriber_name',
        'verification_response',
        'tenant_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'verification_response' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($claim) {
            if (empty($claim->provider_id) && !empty($claim->insurance_provider_id)) {
                $claim->provider_id = $claim->insurance_provider_id;
            }
            if (empty($claim->insurance_provider_id) && !empty($claim->provider_id)) {
                $claim->insurance_provider_id = $claim->provider_id;
            }
            if (empty($claim->total_amount) && !empty($claim->amount)) {
                $claim->total_amount = $claim->amount;
            }
            if (empty($claim->amount) && !empty($claim->total_amount)) {
                $claim->amount = $claim->total_amount;
            }
        });
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function insuranceProvider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }
}
