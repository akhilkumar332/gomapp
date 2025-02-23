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
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->after('id')->unique();
            $table->boolean('phone_verified')->default(false)->after('phone_number');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verified');
            
            // Add unique constraint to phone_number if it doesn't exist
            if (!Schema::hasColumn('users', 'phone_number_unique')) {
                $table->unique('phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone_number_unique')) {
                $table->dropUnique(['phone_number']);
            }
            $table->dropColumn([
                'firebase_uid',
                'phone_verified',
                'phone_verified_at'
            ]);
        });
    }
};
