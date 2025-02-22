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
        // First, create a backup of the existing data
        Schema::rename('activity_logs', 'activity_logs_backup');

        // Create new table with all required columns
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->string('description')->nullable();
            $table->string('device_type')->default('web');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // Copy data from backup table
        DB::statement('INSERT INTO activity_logs (id, user_id, action, device_type, created_at, updated_at) SELECT id, user_id, action, device_type, created_at, updated_at FROM activity_logs_backup');

        // Drop the backup table
        Schema::dropIfExists('activity_logs_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, create a backup of the existing data
        Schema::rename('activity_logs', 'activity_logs_new');

        // Recreate original table structure
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->string('device_type');
            $table->text('details')->nullable();
            $table->timestamps();
        });

        // Copy data back
        DB::statement('INSERT INTO activity_logs (id, user_id, action, device_type, created_at, updated_at) SELECT id, user_id, action, device_type, created_at, updated_at FROM activity_logs_new');

        // Drop the new version table
        Schema::dropIfExists('activity_logs_new');
    }
};
