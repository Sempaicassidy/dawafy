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
        'claim_number',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($claim) {
            if (empty($claim->provider_id) && !empty($claim->insurance_provider_id)) {
                $claim->provider_id = $claim->insurance_provider_id;
            }
            if (empty($claim->total_amount) && !empty($claim->amount)) {
                $claim->total_amount = $claim->amount;
            }
        });
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function insuranceProvider()
    {
        return $this->belongsTo(InsuranceProvider::class);
    }
}
