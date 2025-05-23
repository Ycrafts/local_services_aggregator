<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
        DB::statement("ALTER TABLE jobs DROP CONSTRAINT IF EXISTS jobs_status_check");

       
        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_status_check 
            CHECK (status IN ('open', 'in_progress', 'completed', 'cancelled', 'assigned'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jobs DROP CONSTRAINT IF EXISTS jobs_status_check");

        DB::statement("ALTER TABLE jobs ADD CONSTRAINT jobs_status_check 
            CHECK (status IN ('open', 'in_progress', 'completed', 'cancelled'))");
    }
};

