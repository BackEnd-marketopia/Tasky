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
        Schema::create('package_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_type_id'); // نوع الحزمة
            $table->string('title');
            $table->integer('target_count')->default(0); // العدد المطلوب لهذا النوع
            $table->unsignedBigInteger('workspace_id'); // workspace
            $table->text('description')->nullable(); // وصف الهدف
            $table->boolean('is_active')->default(true); // حالة الهدف
            $table->timestamps();

            // Foreign Keys
            $table->foreign('package_type_id')->references('id')->on('package_types')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            // Unique constraint - كل workspace يمكن أن يكون له هدف واحد فقط لكل نوع حزمة
            $table->unique(['package_type_id', 'workspace_id'], 'unique_workspace_package_goal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_goals');
    }
};
