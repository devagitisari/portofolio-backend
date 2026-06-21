<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Setting extends Model
{
    use Notifiable;

    protected $fillable = [
        'site_name',
        'tagline',
        'name',
        'title',
        'email',
        'whatsapp',
        'bio',
        'about_me',
        'profile_image',
        'resume',
        'github',
        'linkedin',
        'instagram',
        'completed_projects',
        'tech_stack_count',
        'gpa',
        'auto_update_skill_badges',
        'show_learning_curve',
        'show_github_activity',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
    ];

    protected $casts = [
        'auto_update_skill_badges' => 'boolean',
        'show_learning_curve' => 'boolean',
        'show_github_activity' => 'boolean',
    ];
}
