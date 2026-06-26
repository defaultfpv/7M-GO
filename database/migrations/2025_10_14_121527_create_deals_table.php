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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('phone')->nullable();
            $table->text('name')->nullable();
            $table->text('description')->nullable();
            $table->text('coords')->nullable();
            $table->text('address')->nullable();
            $table->text('comment')->nullable();
            $table->text('price')->nullable();
            $table->text('start_at')->nullable();
            $table->text('end_at')->nullable();
            $table->text('status')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
