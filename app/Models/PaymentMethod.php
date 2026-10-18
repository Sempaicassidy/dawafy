<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    protected $fillable = [
        'code',
        'name',
        'display_name',
        'icon',
        'color',
        'description',
        'is_enabled',
        'is_locked',
        'sort_order',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_locked' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all active payment methods.
     */
    public static function getActiveMethods()
    {
        try {
            $methods = self::where('is_enabled', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            // Safety guard: ensure Cash is always present
            if (!$methods->contains('name', 'Cash')) {
                $cash = self::where('name', 'Cash')->first();
                if ($cash) {
                    $methods->prepend($cash);
                }
            }

            return $methods;
        } catch (\Throwable $e) {
            // Fallback if table query fails
            return collect([
                (object)[
                    'id' => 1,
                    'code' => 'cash',
                    'name' => 'Cash',
                    'display_name' => 'Pesa Taslimu (Cash)',
                    'icon' => 'bi-cash-stack',
                    'color' => 'success',
                    'is_enabled' => true,
                    'is_locked' => true
                ]
            ]);
        }
    }

    /**
     * Get list of active payment method names.
     */
    public static function getActiveNames(): array
    {
        try {
            $names = self::where('is_enabled', true)->pluck('name')->toArray();
            if (!in_array('Cash', $names)) {
                $names[] = 'Cash';
            }
            return $names;
        } catch (\Throwable $e) {
            return ['Cash'];
        }
    }

    /**
     * Check if a specific payment method is active/enabled.
     */
    public static function isMethodEnabled(string $name): bool
    {
        // Cash is always enabled by rule
        if (strcasecmp($name, 'cash') === 0) {
            return true;
        }

        try {
            return self::where('name', $name)->where('is_enabled', true)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
