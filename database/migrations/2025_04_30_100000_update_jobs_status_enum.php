<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateJobsStatusEnum extends Migration
{
    public function up()
    {
        // Drop the existing enum constraint
        DB::statement("ALTER TABLE jobs DROP CONSTRAINT IF EXISTS jobs_status_check");

        // Add the updated enum constraint including 'assigned'
        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_status_check CHECK (status IN ('open', 'assigned', 'in_progress', 'completed', 'cancelled'))");
    }

    public function down()
    {
        // Revert to the previous enum constraint
        DB::statement("ALTER TABLE jobs DROP CONSTRAINT IF EXISTS jobs_status_check");
        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_status_check CHECK (status IN ('open', 'in_progress', 'completed', 'cancelled'))");
    }
} 