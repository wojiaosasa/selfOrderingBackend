<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('openid')->unique();
            $table->string('role')->nullable()->index();
            $table->string('nickname')->default('');
            $table->string('avatar_url')->default('');
            $table->string('phone')->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
