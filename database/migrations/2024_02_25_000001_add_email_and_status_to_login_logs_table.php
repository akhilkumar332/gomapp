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
        Schema::table('login_logs', function (Blueprint $table) {
            $table->string('email')->nullable()->after('user_id');
            $table->string('status')->nullable()->after('user_agent');
            // Make user_id nullable since we might log failed attempts without a user
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropColumn(['email', 'status']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
