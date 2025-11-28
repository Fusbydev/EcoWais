<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_reports', function (Blueprint $table) {

            // Add status column
            $table->enum('status', ['pending', 'in_progress', 'completed'])
                ->default('pending')
                ->after('id'); // change 'id' to the appropriate column

            // Add priority column
            $table->enum('priority', ['hard', 'medium', 'low'])
                ->default('low')
                ->after('status');

            // Add location column
            $table->string('location')
                ->nullable()
                ->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('driver_reports', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority', 'location']);
        });
    }
};
