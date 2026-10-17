<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'batch_number',
        'quantity',
        'manufacture_date',
        'expiry_date',
        'supplier_id',
        'tenant_id',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function quarantine()
    {
        return $this->hasOne(Quarantine::class);
    }

    public function isExpired()
    {
        return $this->expiry_date < now();
    }
}
