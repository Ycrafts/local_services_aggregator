<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('requested_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_profile_id')->constrained()->onDelete('cascade');
            $table->boolean('is_interested')->nullable(); // null = no response yet, true/false = provider responded
            $table->timestamps();
        });   
    }

    public function down(): void
    {
        Schema::dropIfExists('requested_jobs');
    }
};
