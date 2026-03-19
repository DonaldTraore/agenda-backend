<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('has_reminder')->default(false);
            $table->integer('reminder_minutes')->nullable(); // Minutes avant l'événement
            $table->boolean('reminder_sent')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['has_reminder', 'reminder_minutes', 'reminder_sent']);
        });
    }
};