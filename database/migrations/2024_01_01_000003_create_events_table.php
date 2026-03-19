<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->string('location')->nullable();
            $table->string('color', 7)->default('#3788d8');
            $table->boolean('is_all_day')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'start_date']);
            $table->index(['user_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};