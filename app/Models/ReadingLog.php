<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'digital_book_id',
        'last_page_read',
        'total_reading_time',
        'last_accessed_at'
    ];

    protected $casts = [
        'last_page_read' => 'integer',
        'total_reading_time' => 'integer',
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the reading log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the digital book that owns the reading log.
     */
    public function digitalBook(): BelongsTo
    {
        return $this->belongsTo(DigitalBook::class);
    }

    /**
     * Get formatted reading time.
     */
    public function getFormattedReadingTimeAttribute(): string
    {
        $minutes = $this->total_reading_time;
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }

        return "{$remainingMinutes} menit";
    }

    /**
     * Get reading progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->digitalBook && $this->digitalBook->total_pages > 0) {
            return round(($this->last_page_read / $this->digitalBook->total_pages) * 100, 1);
        }

        return 0;
    }
}
