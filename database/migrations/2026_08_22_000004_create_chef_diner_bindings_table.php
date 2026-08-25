<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chef_diner_bindings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chef_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('diner_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['chef_id', 'diner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chef_diner_bindings');
    }
};
