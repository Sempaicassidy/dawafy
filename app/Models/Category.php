<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'tenant_id',
    ];

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
