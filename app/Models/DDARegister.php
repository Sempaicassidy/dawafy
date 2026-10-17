<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DDARegister extends Model
{
    use HasFactory;

    protected $table = 'dda_register';

    protected $fillable = [
        'transaction_item_id',
        'patient_name',
        'id_type',
        'id_number',
        'doctor_name',
        'approved_by_user_id',
    ];

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
