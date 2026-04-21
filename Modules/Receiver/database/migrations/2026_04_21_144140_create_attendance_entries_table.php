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
        Schema::create('attendance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('emp_id');        // raw employee id from device
            $table->string('real_emp_id')->nullable(); // mapped real employee id
            $table->dateTime('check_time');
            $table->string('branch')->nullable();
            $table->string('device_name')->nullable();
            $table->unique(['organization_id', 'device_id', 'emp_id', 'check_time'], 'att_entries_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_entries');
    }
};
