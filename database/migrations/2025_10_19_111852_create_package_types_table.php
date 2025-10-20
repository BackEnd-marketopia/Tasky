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
        Schema::create('package_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم نوع الحزمة
            $table->string('icon')->nullable(); // أيقونة نوع الحزمة (FontAwesome, etc.)
            $table->string('color')->default('#007bff'); // لون نوع الحزمة
            $table->text('description')->nullable(); // وصف نوع الحزمة
            $table->unsignedBigInteger('workspace_id'); // ربط بـ workspace
            $table->boolean('is_active')->default(true); // هل النوع نشط أم لا
            $table->timestamps();

            // Foreign Key
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['name', 'workspace_id'], 'unique_package_type_per_workspace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_types');
    }
};
