<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'project_role')) {
                $table->string('project_role')->nullable()->after('category');
            }
            if (!Schema::hasColumn('projects', 'problem')) {
                $table->text('problem')->nullable()->after('long_description');
            }
            if (!Schema::hasColumn('projects', 'solution')) {
                $table->text('solution')->nullable()->after('problem');
            }
            if (!Schema::hasColumn('projects', 'key_features')) {
                $table->json('key_features')->nullable()->after('solution');
            }
            if (!Schema::hasColumn('projects', 'impact')) {
                $table->text('impact')->nullable()->after('key_features');
            }
            if (!Schema::hasColumn('projects', 'tools')) {
                $table->json('tools')->nullable()->after('tags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            foreach (['tools', 'impact', 'key_features', 'solution', 'problem', 'project_role'] as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
