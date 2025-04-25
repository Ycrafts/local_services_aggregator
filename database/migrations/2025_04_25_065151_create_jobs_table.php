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
            $table->decimal('proposed_price', 8, 2); // set by customer
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->timestamps();
        });        
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
