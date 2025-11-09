<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trucks', function (Blueprint $table) {
            $table->id(); // Primary auto-increment ID
            $table->string('truck_id')->unique(); // The actual truck identifier
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade'); // link to driver
            $table->string('initial_location');
            $table->integer('initial_fuel')->default(100);
            $table->string('status')->default('idle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};

