<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('assigned_provider_id')->nullable()->constrained('provider_profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['assigned_provider_id']);
            $table->dropColumn('assigned_provider_id');
        });
    }
};
