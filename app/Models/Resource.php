<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'download_count',
        'is_public',
        'is_downloadable'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}