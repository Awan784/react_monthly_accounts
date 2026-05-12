<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_deals', function (Blueprint $table) {
            $table->boolean('contract_legacy_json')->default(false)->after('contract_size');
        });

        // One-time: rows that still have inline JSON (no file path) are downloadable via GET /contract
        DB::table('brand_deals')
            ->whereNull('contract_path')
            ->whereNotNull('contract')
            ->update(['contract_legacy_json' => true]);
    }

    public function down(): void
    {
        Schema::table('brand_deals', function (Blueprint $table) {
            $table->dropColumn('contract_legacy_json');
        });
    }
};
