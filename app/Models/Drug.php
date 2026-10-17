<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_name',
        'generic_name',
        'category_id',
        'sub_category_id',
        'tmda_reg_number',
        'reg_no',
        'manufacturer',
        'manufacture_lic_no',
        'strength',
        'dosage_form',
        'barcode',
        'reorder_level',
        'price',
        'buying_price',
        'quantity',
        'tenant_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'buying_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStockAttribute()
    {
        return $this->batches()->sum('quantity');
    }

    public function getOutOfStockAttribute()
    {
        return $this->stock <= $this->reorder_level;
    }
}
