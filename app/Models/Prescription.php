<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_item_id',
        'patient_name',
        'doctor_name',
        'clinic_name',
        'image_path',
        'notes',
    ];

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }
}
