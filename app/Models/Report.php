<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}