<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade'); // only customers
            $table->foreignId('job_type_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->decimal('estimated_cost', 8, 2); // renamed from proposed_price, for reference only
            $table->foreignId('assigned_provider_id')->nullable()->constrained('provider_profiles')->onDelete('set null');
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->timestamps();
        });        
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
