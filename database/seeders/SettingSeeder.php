<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'site_name' => 'Deva Gitisari | Portofolio',
            'name' => 'Deva Gitisari',
            'title' => 'Informatics Student',
            'email' => 'devagitisari96@gmail.com',
            'whatsapp' => '+62 858 8820 2616',
            'bio' => 'I am a passionate full stack developer with expertise in Laravel, React, and modern web technologies.',
            'github' => 'https://github.com/devagitisari',
            'linkedin' => 'https://linkedin.com/in/devagitisari',
            'instagram' => 'https://instagram.com/devagitisari',
            'completed_projects' => 15,
            'tech_stack_count' => 12,
            'gpa' => '4.0',
        ]);
    }
}
