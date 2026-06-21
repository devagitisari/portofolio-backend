<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'category',
        'percentage',
        'show_on_home',
        'calculated_percentage',
        'project_count',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'show_on_home' => 'boolean',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * Auto-calculated percentage based on how many projects use this skill
     * relative to the total number of projects.
     * Falls back to manual percentage if there are no projects yet.
     */
    public function getCalculatedPercentageAttribute(): int
    {
        $totalProjects = Project::count();
        if ($totalProjects === 0) {
            return $this->percentage;
        }
        $usedIn = $this->projects()->count();
        return (int) round(($usedIn / $totalProjects) * 100);
    }
}
