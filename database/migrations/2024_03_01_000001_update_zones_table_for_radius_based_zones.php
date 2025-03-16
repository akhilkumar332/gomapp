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
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('boundaries'); // Remove boundaries field
            $table->decimal('center_lat', 10, 8)->nullable()->after('status'); // Latitude
            $table->decimal('center_lng', 11, 8)->nullable()->after('center_lat'); // Longitude
            $table->unsignedInteger('radius')->nullable()->after('center_lng'); // Radius in meters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->json('boundaries')->nullable()->after('status'); // Re-add boundaries field
            $table->dropColumn(['center_lat', 'center_lng', 'radius']); // Remove new fields
        });
    }
};
