<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->renameColumn('initial_latitude', 'current_latitude');
            $table->renameColumn('initial_longitude', 'current_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->renameColumn('current_latitude', 'initial_latitude');
            $table->renameColumn('current_longitude', 'initial_longitude');
        });
    }
};
