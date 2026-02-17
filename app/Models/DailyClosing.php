<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'closing_date',
        'operating_day',
        'is_manually_started',
        'total_revenue',
        'total_transactions',
        'total_items',
        'closed_by',
        'notes',
        'new_day_started_at', // 👈 ADD THIS
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'total_revenue' => 'decimal:2',
            'is_manually_started' => 'boolean',
            'new_day_started_at' => 'datetime', // 👈 ADD THIS
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Check if a specific date is closed.
     */
    public static function isDateClosed($date): bool
    {
        return self::whereDate('closing_date', $date)->exists();
    }

    /**
     * Get closing for specific date.
     */
    public static function getClosing($date): ?self
    {
        return self::whereDate('closing_date', $date)->first();
    }

    /**
     * Check if we're currently in a closed state (not started new day yet).
     * CRITICAL: This checks if the LAST closing has been "reopened" with Start New Day.
     */
    public static function isCurrentlyClosed(): bool
    {
        $lastClosing = self::latest('created_at')->first();
        
        if (!$lastClosing) {
            return false; // No closing yet = open
        }

        // If new_day_started_at is set, it means we started a new day
        // So we're NOT closed anymore
        return $lastClosing->new_day_started_at === null;
    }

    /**
     * Get current operating day number.
     */
    public static function getCurrentOperatingDay(): int
    {
        $latest = self::latest('created_at')->first();
        return $latest ? $latest->operating_day + 1 : 1;
    }

    /**
     * Start a new operating day (mark the last closing as "reopened").
     */
    public static function startNewOperatingDay(): void
    {
        $lastClosing = self::latest('created_at')->first();
        
        if ($lastClosing && $lastClosing->new_day_started_at === null) {
            $lastClosing->update([
                'new_day_started_at' => now()
            ]);
        }
    }

    /**
     * Check if can start new day (must have a closing that hasn't been reopened).
     */
    public static function canStartNewDay(): bool
    {
        $lastClosing = self::latest('created_at')->first();
        
        if (!$lastClosing) {
            return false; // No closing yet
        }

        // Can start new day if the last closing hasn't been "reopened" yet
        return $lastClosing->new_day_started_at === null;
    }
}