<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->json('completed_routes')->nullable()->comment('Stores JSON of completed routes');
        });
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('completed_routes');
        });
    }
};
