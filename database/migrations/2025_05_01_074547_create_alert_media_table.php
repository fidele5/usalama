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
        Schema::create('alert_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alert_id');
            $table->string('file_path');
            $table->string('file_type'); // 'image', 'video'
            $table->foreign('alert_id')->references('id')->on('alerts')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_media');
    }
};
