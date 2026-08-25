<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_no')->unique();
            $table->foreignId('chef_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('diner_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->dateTime('expected_time')->nullable();
            $table->text('note')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->index(['chef_id', 'status', 'created_at']);
            $table->index(['diner_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
