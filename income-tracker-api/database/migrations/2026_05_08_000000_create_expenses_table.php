<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('client_id', 64);

            $table->date('date')->nullable();
            $table->string('month', 20);
            $table->unsignedInteger('year');
            $table->string('category', 120);
            $table->string('platform', 120)->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('receipt_disk', 20)->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime')->nullable();
            $table->unsignedBigInteger('receipt_size')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->index(['user_id', 'year', 'month']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

