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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('external_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('name')->nullable();
            $table->text('token')->nullable();
            $table->text('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('device_type')->nullable();
            $table->text('browser')->nullable();
            $table->text('platform')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
