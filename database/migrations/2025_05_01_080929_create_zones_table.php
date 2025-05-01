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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->geometry('boundaries'); // Spatial data type for storing polygon boundaries
            $table->enum('priority_level', ['low', 'medium', 'high']);
            $table->timestamps();
            $table->softDeletes();

            $table->spatialIndex('boundaries'); // Index spatial
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
