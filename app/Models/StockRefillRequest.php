<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRefillRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'pharmacist_id',
        'drug_id',
        'requested_quantity',
        'approved_quantity',
        'status',
        'manager_decision',
        'manager_id',
        'notes',
    ];

    public function pharmacist()
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}
