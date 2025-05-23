<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First drop any existing constraint
        DB::statement('ALTER TABLE requested_jobs DROP CONSTRAINT IF EXISTS requested_jobs_status_check');

        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_interested');
        });

        // Add check constraint for status values
        DB::statement("ALTER TABLE requested_jobs ADD CONSTRAINT requested_jobs_status_check 
            CHECK (status IN ('pending', 'interested', 'selected', 'offer_accepted', 'declined'))");
    }

    public function down(): void
    {
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
