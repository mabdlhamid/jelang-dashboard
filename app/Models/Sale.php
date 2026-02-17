<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'transaction_date',
        'quantity',
        'total_price',
        'payment_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Get the menu that owns the sale.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Scope: Filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by payment status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Filter by paid transactions only.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Get the hour of transaction.
     */
    public function getTransactionHourAttribute(): int
    {
        return $this->transaction_date->format('H');
    }

    /**
     * Check if this sale is locked (date is closed).
     */
   

    /**
     * Check if a specific date is locked.
     */
    public static function isDateLocked($date): bool
    {
        return DailyClosing::isDateClosed($date);
    }

    /**
     * Scope: Get today's sales only.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', now()->toDateString());
    }

    /**
     * Scope: Get sales that are not locked.
     */
    public function scopeUnlocked($query)
    {
        $closedDates = DailyClosing::pluck('closing_date')->toArray();
        return $query->whereNotIn(
            \DB::raw('DATE(transaction_date)'),
            $closedDates
        );
    }

    /**
     * Get today's summary (dynamic calculation).
     */
  /**
 * Get today's summary (for current operating day).
 */


/**
 * Get current operating period summary.
 * Shows transactions AFTER the last closing timestamp.
 */
public static function getTodaySummary(): array
{
    $lastClosing = \App\Models\DailyClosing::latest('created_at')->first();
    
    if (!$lastClosing) {
        // No closing yet - show all paid transactions from today
        $sales = self::where('payment_status', 'paid')
            ->whereDate('transaction_date', now()->toDateString())
            ->get();
    } else {
        // CRITICAL: Show only transactions AFTER the last closing
        // This ensures when you start new day, only NEW transactions count
        $sales = self::where('payment_status', 'paid')
            ->where('transaction_date', '>', $lastClosing->created_at)
            ->get();
    }

    return [
        'total_revenue' => $sales->sum('total_price'),
        'total_transactions' => $sales->count(),
        'total_items' => $sales->sum('quantity'),
    ];
}
    
   
/**
 * Check if this sale is locked (before last closing).
 */
public function isLocked(): bool
{
    $lastClosing = \App\Models\DailyClosing::latest('created_at')->first();
    
    if (!$lastClosing) {
        return false; // No closings yet, nothing is locked
    }

    // Lock if transaction is BEFORE the last closing time
    return $this->transaction_date <= $lastClosing->created_at;
}
}