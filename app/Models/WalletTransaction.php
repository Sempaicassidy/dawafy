<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($wt) {
            if (empty($wt->transaction_type) && !empty($wt->type)) {
                $wt->transaction_type = strtolower($wt->type);
            }
            if (empty($wt->type) && !empty($wt->transaction_type)) {
                $wt->type = ucfirst($wt->transaction_type);
            }
        });
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
