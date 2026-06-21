<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'auto_update_skill_badges')) {
                $table->boolean('auto_update_skill_badges')->default(true)->after('gpa');
            }
            if (!Schema::hasColumn('settings', 'show_github_activity')) {
                $table->boolean('show_github_activity')->default(true)->after('auto_update_skill_badges');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'show_github_activity')) {
                $table->dropColumn('show_github_activity');
            }
            if (Schema::hasColumn('settings', 'auto_update_skill_badges')) {
                $table->dropColumn('auto_update_skill_badges');
            }
        });
    }
};
