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
        Schema::create('zone_responders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained();
            $table->foreignId('responder_id')->constrained();
            $table->boolean('is_primary')->default(false);
            $table->softDeletes(); // Soft delete for historical data retention
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_responders');
    }
};
