<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'title',
        'description',
        'parameters',
        'data',
        'file_path',
        'status',
        'generated_at'
    ];

    protected $casts = [
        'parameters' => 'array',
        'data' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Define the generator of the report.
     */
    // Inside App\Models\Report.php

public function scopeForUser($query, $user)
{
    if ($user->hasRole('admin')) {
        return $query; // Admin sees everything
    }
    return $query->where('user_id', $user->id);
}
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to check if report is ready.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}