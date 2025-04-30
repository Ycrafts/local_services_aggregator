<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->boolean('is_selected')->default(false);
        });
    }
    

    public function down(): void
    {
        Schema::table('requested_jobs', function (Blueprint $table) {
            //
        });
    }
};
