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
        Schema::create('monthly_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Client-generated id from the prototype (e.g. "mok..." / uid()) for easy sync
            $table->string('client_id', 64);

            $table->string('month', 20);
            $table->unsignedInteger('year');
            $table->string('platform', 120);
            $table->string('account', 120);

            $table->decimal('income', 12, 2)->default(0);
            $table->decimal('gmv', 12, 2)->default(0);
            $table->unsignedInteger('videos')->default(0);
            $table->unsignedInteger('items_sold')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            // Enforce "monthly total replaces previous one" rule:
            $table->unique(['user_id', 'month', 'year', 'platform', 'account']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_totals');
    }
};
