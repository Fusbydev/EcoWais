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
            $table->decimal('initial_latitude', 10, 7)->nullable()->after('id');
            $table->decimal('initial_longitude', 10, 7)->nullable()->after('initial_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn(['initial_latitude', 'initial_longitude']);
        });
    }
};
