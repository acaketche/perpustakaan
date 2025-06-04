<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the digital books uploaded by the user.
     */
    public function uploadedBooks(): HasMany
    {
        return $this->hasMany(DigitalBook::class, 'uploaded_by');
    }

    /**
     * Get the reading logs for the user.
     */
    public function readingLogs(): HasMany
    {
        return $this->hasMany(ReadingLog::class);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is dosen.
     */
    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    /**
     * Check if user is mahasiswa.
     */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    /**
     * Check if user can upload books.
     */
    public function canUploadBooks(): bool
    {
        return in_array($this->role, ['admin', 'dosen']);
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'dosen' => 'Dosen',
            'mahasiswa' => 'Mahasiswa',
            default => 'Unknown'
        };
    }

    /**
     * Get total reading time for user.
     */
    public function getTotalReadingTimeAttribute(): int
    {
        return $this->readingLogs()->sum('total_reading_time');
    }

    /**
     * Get total books read by user.
     */
    public function getTotalBooksReadAttribute(): int
    {
        return $this->readingLogs()->distinct('digital_book_id')->count();
    }
}
