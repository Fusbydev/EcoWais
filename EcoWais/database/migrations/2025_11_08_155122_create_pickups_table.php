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
    Schema::create('pickups', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('location_id'); // foreign key to locations
        $table->unsignedBigInteger('truck_id');    // foreign key to trucks
        $table->date('pickup_date');
        $table->time('pickup_time');
        $table->timestamps();

        // Foreign key constraints
        $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        $table->foreign('truck_id')->references('id')->on('trucks')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};
