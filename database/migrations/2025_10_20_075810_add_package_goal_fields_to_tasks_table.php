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
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('package_goal_id')->nullable()->after('project_id');
            $table->integer('progress_count')->default(0)->after('package_goal_id');
            
            // Foreign key constraint
            $table->foreign('package_goal_id')->references('id')->on('package_goals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['package_goal_id']);
            $table->dropColumn(['package_goal_id', 'progress_count']);
        });
    }
};
