<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Issue Type (missed, spillage, illegal, damaged, driver-absent, vehicle, other)
            $table->string('issue_type');

            // If issue_type = 'other'
            $table->string('other_issue')->nullable();

            // For "Driver/Collector Absent"
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');

            // Location typed by user
            $table->string('location');

            // Date & Time of incident
            $table->dateTime('incident_datetime');

            // Priority (low, medium, high)
            $table->enum('priority', ['low', 'medium', 'high']);

            // Description textarea
            $table->text('description');

            // Photo path (optional)
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
