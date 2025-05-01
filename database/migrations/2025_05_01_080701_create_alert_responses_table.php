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
        Schema::create('alert_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained();
            $table->foreignId('responder_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Agent en charge
            $table->text('action_taken');
            $table->enum('status', ['dispatched', 'on_scene', 'completed', 'cancelled']);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes(); // Soft delete for historical data retention
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_responses');
    }
};
