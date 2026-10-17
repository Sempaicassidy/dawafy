<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'batch_id',
        'drug_id',
        'quantity',
        'dosage',
        'price',
        'tenant_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function ddaRegister()
    {
        return $this->hasOne(DDARegister::class);
    }
}
