<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'long_description',
        'problem',
        'solution',
        'key_features',
        'impact',
        'category',
        'project_role',
        'status',
        'thumbnail',
        'demo_url',
        'github_url',
        'featured',
        'start_date',
        'end_date',
    ];

    protected $attributes = [
        'status' => 'published',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'key_features' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });

        static::updating(function ($project) {
            if ($project->isDirty('title') && empty($project->slug)) {
                $project->slug = Str::slug($project->title);
            }
        });
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }
}
