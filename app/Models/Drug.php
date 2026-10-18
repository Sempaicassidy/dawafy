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

    public function refillRequests()
    {
        return $this->hasMany(StockRefillRequest::class);
    }

    public function getStockAttribute()
    {
        return (int)$this->batches()->sum('quantity');
    }

    public function getOutOfStockAttribute()
    {
        return $this->stock <= 0;
    }

    public function getIsLowStockAttribute()
    {
        $reorder = (int)($this->reorder_level ?: 10);
        return $this->stock > 0 && $this->stock <= $reorder;
    }

    public function getStockLightAttribute()
    {
        if ($this->stock <= 0) {
            return 'red'; // Out of stock
        }
        $reorder = (int)($this->reorder_level ?: 10);
        if ($this->stock <= $reorder) {
            return 'yellow'; // Low stock
        }
        return 'green'; // In stock
    }

    public function getTmdaShortAttribute()
    {
        $cat = strtolower($this->category?->name ?? '');
        if (str_contains($cat, 'prescription') || str_contains($cat, 'pom')) {
            return 'POM';
        }
        if (str_contains($cat, 'pharmacy') || str_contains($cat, 'p-only') || str_contains($cat, 'p medicine')) {
            return 'P';
        }
        if (str_contains($cat, 'controlled')) {
            return 'Controlled';
        }
        if (str_contains($cat, 'general') || str_contains($cat, 'gsl') || str_contains($cat, 'gsm') || str_contains($cat, 'otc')) {
            return 'GSL';
        }
        if (str_contains($cat, 'addo')) {
            return 'ADDO';
        }
        return 'POM';
    }
}
