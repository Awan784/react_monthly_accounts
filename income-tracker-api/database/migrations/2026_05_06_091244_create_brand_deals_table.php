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
        Schema::create('brand_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('client_id', 64);

            $table->date('date')->nullable();
            $table->string('month', 20);
            $table->unsignedInteger('year');
            $table->string('platform', 120);
            $table->string('account', 120);

            $table->string('brand', 255)->default('');
            $table->string('contact', 255)->nullable();
            $table->string('product', 255)->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 20)->default('Pending');
            $table->date('due_date')->nullable();
            $table->string('usage_rights', 255)->nullable();

            // Keep prototype working: stores {name,type,data,savedAt} as JSON (can migrate to S3 later)
            $table->json('contract')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->index(['user_id', 'year', 'month']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_deals');
    }
};
