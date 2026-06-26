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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('external_id')->nullable();
            $table->text('phone')->nullable();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('description')->nullable();
            $table->text('role')->default('worker');
            $table->text('status')->default('active');
            $table->text('color')->default('#d9d9d9');
            $table->text('email')->nullable();
            $table->text('messanger')->nullable();
            $table->text('chat_id')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
