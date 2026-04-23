<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Course;

class Category extends Model
{
    use HasFactory;

    // ✅ Fillable fields
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
    ];

    // ✅ Auto slug generation (create + update safe)
    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = $category->slug ?? Str::slug($category->name);
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // ✅ IMPORTANT: relationships
    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id', 'id');
    }

    // ✅ Optional: route binding (recommended for SaaS)
    public function getRouteKeyName()
    {
        return 'id'; // change to 'slug' if you want /category/web-dev
    }
}