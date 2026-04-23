<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\ProgressTracking;
class Lesson extends Model
{
    use HasFactory;

    protected $primaryKey = 'id'; // 🔥 CHANGE THIS
    
    protected $fillable = [
        'section_id',
        'title',
        'content',
        'order_position',
        'video_url',
        'duration_minutes',
        'is_free_preview',
    ];

    protected $casts = [
        'is_free_preview' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function progress()
    {
        return $this->hasMany('App\Models\ProgressTracking', 'lesson_id', 'lesson_id');
    }
}