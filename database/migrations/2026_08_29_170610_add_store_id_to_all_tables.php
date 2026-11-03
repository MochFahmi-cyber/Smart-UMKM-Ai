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
        // Create Default Store for existing data to prevent breakage
        $defaultStoreId = \Illuminate\Support\Facades\DB::table('stores')->insertGetId([
            'name' => 'Toko Default (Migrasi Lama)',
            'slug' => 'toko-default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tables = [
            'users', 
            'products', 
            'categories', 
            'customers', 
            'transactions', 
            'customer_cashier_tokens', 
            'cash_flows'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($defaultStoreId) {
                $table->foreignId('store_id')->default($defaultStoreId)->constrained('stores')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users', 
            'products', 
            'categories', 
            'customers', 
            'transactions', 
            'customer_cashier_tokens', 
            'cash_flows'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'store_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (\Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite') {
                        $table->dropForeign([$tableName . '_store_id_foreign']);
                    }
                    $table->dropColumn('store_id');
                });
            }
        }
    }
};
