<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Skill;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:recalculate-skills-percentage')]
#[Description('Recalculate calculated_percentage for all skills based on project usage')]
class RecalculateSkillsPercentage extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalProjects = Project::count();
        $skills = Skill::all();
        $totalSkills = $skills->count();

        $this->info("Recalculating skills percentage based on {$totalProjects} total projects and {$totalSkills} total skills...");

        foreach ($skills as $skill) {
            $projectCount = $skill->projects()->count();
            
            // Calculate percentage based on skill usage relative to total skills
            // If a skill is used in projects, distribute 100% among all used skills
            $usedSkillsCount = $skills->filter(fn($s) => $s->projects()->count() > 0)->count();
            
            if ($usedSkillsCount > 0 && $projectCount > 0) {
                $calculatedPercentage = (int) round((100 / $usedSkillsCount));
            } else {
                $calculatedPercentage = 0;
            }

            $skill->update([
                'calculated_percentage' => $calculatedPercentage,
                'project_count' => $projectCount,
            ]);

            $this->info("Updated {$skill->name}: {$calculatedPercentage}% (used in {$projectCount} projects)");
        }

        $this->info('Skills percentage recalculation completed successfully!');
    }
}
