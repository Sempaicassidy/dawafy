<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarantine extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'reason',
        'quarantine_date',
        'notes',
        'action_by',
        'tenant_id',
    ];

    protected $casts = [
        'quarantine_date' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
