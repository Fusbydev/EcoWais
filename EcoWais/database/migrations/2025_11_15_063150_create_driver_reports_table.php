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
        Schema::create('driver_reports', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('driver_id'); // Foreign key to drivers table
            $table->string('issue_type'); // Issue type
            $table->text('description'); // Issue description
            $table->timestamps(); // created_at and updated_at

            // Optional: Foreign key constraint
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_reports');
    }
};
