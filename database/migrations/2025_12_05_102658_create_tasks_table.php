<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('time'); // Store as string for flexibility
            $table->string('location');
            $table->string('day');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('day');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_schedules');
    }
};