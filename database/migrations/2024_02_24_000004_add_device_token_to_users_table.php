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
            $table->string('device_token')->nullable()->after('remember_token');
            
            // Add index for phone verification if it doesn't exist
            if (!Schema::hasIndex('users', 'users_phone_number_index')) {
                $table->index(['phone_number']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_phone_number_index')) {
                $table->dropIndex(['phone_number']);
            }
            $table->dropColumn('device_token');
        });
    }
};
