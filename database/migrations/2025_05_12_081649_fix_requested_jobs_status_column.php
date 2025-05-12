<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the existing status column and its constraint
        DB::statement('ALTER TABLE requested_jobs DROP CONSTRAINT IF EXISTS requested_jobs_status_check');
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Then add the status column back with proper configuration
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_interested');
        });

        // Add the check constraint
        DB::statement("ALTER TABLE requested_jobs ADD CONSTRAINT requested_jobs_status_check 
            CHECK (status IN ('pending', 'interested', 'selected', 'offer_accepted', 'declined'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the check constraint
        DB::statement('ALTER TABLE requested_jobs DROP CONSTRAINT IF EXISTS requested_jobs_status_check');
        
        // Drop the status column
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
