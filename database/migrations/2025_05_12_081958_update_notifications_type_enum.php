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
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');

        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check 
            CHECK (type IN ('new_job', 'job_selected', 'status_change', 'provider_interested', 'provider_assigned'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check 
            CHECK (type IN ('new_job', 'job_selected', 'status_change'))");
    }
};
