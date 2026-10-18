<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pharmacist_id',
        'cashier_id',
        'shift_id',
        'customer_id',
        'total_amount',
        'payment_method',
        'status',
        'dispensed_at',
        'tenant_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'dispensed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'customer_id');
    }

    public function customer()
    {
        return $this->belongsTo(Patient::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function insuranceClaim()
    {
        return $this->hasOne(InsuranceClaim::class);
    }
}
