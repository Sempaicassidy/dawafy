<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'opening_balance',
        'closing_balance',
        'notes',
        'tenant_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($shift) {
            if (empty($shift->opening_cash) && isset($shift->opening_balance)) {
                $shift->opening_cash = $shift->opening_balance;
            }
            if (empty($shift->status)) {
                $shift->status = 'Active';
            }
        });

        static::updating(function ($shift) {
            if (isset($shift->closing_balance)) {
                $shift->actual_cash = $shift->closing_balance;
                $shift->status = 'Closed';
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
