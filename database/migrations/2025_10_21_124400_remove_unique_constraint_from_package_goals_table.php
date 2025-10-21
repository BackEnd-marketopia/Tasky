<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check what indexes exist on the table
        $indexes = DB::select("SHOW INDEX FROM package_goals");
        $indexNames = [];
        
        foreach ($indexes as $index) {
            if (!in_array($index->Key_name, ['PRIMARY', 'package_goals_workspace_id_foreign', 'package_goals_package_type_id_foreign'])) {
                $indexNames[] = $index->Key_name;
            }
        }
        
        // Remove unique constraints that contain workspace and package
        foreach ($indexNames as $indexName) {
            if (strpos($indexName, 'unique') !== false && 
                (strpos($indexName, 'workspace') !== false || strpos($indexName, 'package') !== false)) {
                try {
                    DB::statement("ALTER TABLE package_goals DROP INDEX `{$indexName}`");
                    echo "Dropped index: {$indexName}\n";
                } catch (\Exception $e) {
                    echo "Could not drop index: {$indexName} - " . $e->getMessage() . "\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_goals', function (Blueprint $table) {
            // Restore the unique constraint
            $table->unique(['package_type_id', 'workspace_id'], 'unique_workspace_package_goal');
        });
    }
};
