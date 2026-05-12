<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_deals', function (Blueprint $table) {
            $table->string('contract_disk', 20)->nullable()->after('contract');
            $table->string('contract_path')->nullable()->after('contract_disk');
            $table->string('contract_original_name')->nullable()->after('contract_path');
            $table->string('contract_mime', 120)->nullable()->after('contract_original_name');
            $table->unsignedBigInteger('contract_size')->nullable()->after('contract_mime');
        });
    }

    public function down(): void
    {
        Schema::table('brand_deals', function (Blueprint $table) {
            $table->dropColumn([
                'contract_disk',
                'contract_path',
                'contract_original_name',
                'contract_mime',
                'contract_size',
            ]);
        });
    }
};
