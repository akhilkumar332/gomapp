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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('contact_number');
            $table->string('payment_status')->nullable()->after('payment_method');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_status');
            $table->index(['payment_method', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['payment_method', 'payment_status']);
            $table->dropColumn(['payment_method', 'payment_status', 'payment_amount']);
        });
    }
};
