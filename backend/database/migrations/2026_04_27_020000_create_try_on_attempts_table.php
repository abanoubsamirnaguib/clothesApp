<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('try_on_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('user_key', 64);
            $table->string('status')->default('reserved'); // reserved|completed|failed
            $table->string('person_image_url')->nullable();
            $table->string('garment_image_url')->nullable();
            $table->string('result_image_url')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'user_key']);
            $table->index(['user_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('try_on_attempts');
    }
};

