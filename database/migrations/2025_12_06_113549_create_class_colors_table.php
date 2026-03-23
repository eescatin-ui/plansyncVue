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
        Schema::create('class_colors', function (Blueprint $table) {
            $table->id();
            $table->string('class_name')->unique(); // Ensure each class has only one color
            $table->string('color')->default('#6c757d'); // Store as hex or rgba
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_colors');
    }
};