<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->enum('status', ['interested', 'selected', 'offer_accepted', 'declined'])
                  ->nullable()
                  ->after('is_interested');
        });
    }

    public function down(): void
    {
        Schema::table('requested_jobs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
