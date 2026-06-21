<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'name')) {
                $table->string('name')->nullable()->after('site_name');
            }
            if (!Schema::hasColumn('settings', 'title')) {
                $table->string('title')->nullable()->after('tagline');
            }
            if (!Schema::hasColumn('settings', 'email')) {
                $table->string('email')->nullable()->after('title');
            }
            if (!Schema::hasColumn('settings', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('email');
            }
            if (!Schema::hasColumn('settings', 'completed_projects')) {
                $table->unsignedInteger('completed_projects')->default(0)->after('whatsapp');
            }
            if (!Schema::hasColumn('settings', 'tech_stack_count')) {
                $table->unsignedInteger('tech_stack_count')->default(0)->after('completed_projects');
            }
            if (!Schema::hasColumn('settings', 'gpa')) {
                $table->string('gpa')->nullable()->after('tech_stack_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('settings', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('settings', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('settings', 'whatsapp')) {
                $table->dropColumn('whatsapp');
            }
            if (Schema::hasColumn('settings', 'completed_projects')) {
                $table->dropColumn('completed_projects');
            }
            if (Schema::hasColumn('settings', 'tech_stack_count')) {
                $table->dropColumn('tech_stack_count');
            }
            if (Schema::hasColumn('settings', 'gpa')) {
                $table->dropColumn('gpa');
            }
        });
    }
};
