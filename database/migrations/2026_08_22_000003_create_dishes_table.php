<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chef_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('image_url')->default('');
            $table->string('category');
            $table->text('recipe')->nullable();
            $table->string('taste_note')->nullable();
            $table->string('price')->nullable();
            $table->string('portion_note')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chef_id', 'status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
