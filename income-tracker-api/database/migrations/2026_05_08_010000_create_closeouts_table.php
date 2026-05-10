<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closeouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('month', 20);
            $table->unsignedInteger('year');

            $table->boolean('tiktok')->default(false);
            $table->boolean('brands')->default(false);
            $table->boolean('expenses')->default(false);
            $table->boolean('backup')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closeouts');
    }
};

