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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->uuid('public_id')->unique(); // UUID for public access api
            
            // User and alert type
            $table->unsignedBigInteger('alert_type_id');
            $table->unsignedBigInteger('user_id');
            $table->string('contact_phone')->nullable();
            
            // Alert details
            $table->text('description');
            $table->geometry('location')->nullable();
            $table->string('address')->nullable();
            
            // Workflow status
            $table->enum('status', ['pending', 'resolved', 'canceled'])->default('pending');
            $table->enum('priority', ['normal', 'medium', 'urgent'])->default('normal');
            $table->timestamp('resolved_at')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger("canceled_by")->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('alerts', function (Blueprint $table) {
            $table->foreign('alert_type_id')->references('id')->on('alert_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('canceled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
