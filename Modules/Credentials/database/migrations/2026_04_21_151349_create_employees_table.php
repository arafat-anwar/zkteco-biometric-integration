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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedBigInteger('mdb_user_id')->comment('USERID from MDB USERINFO');
            $table->string('badge_number', 50)->nullable()->comment('Badgenumber from MDB');
            $table->string('name', 255);
            $table->string('card_no', 100)->nullable();
            $table->unsignedInteger('department_id')->nullable()->comment('DEFAULTDEPTID from MDB');
            $table->unsignedTinyInteger('privilege')->default(0)->comment('0=normal,1=enroller,2=manager,3=admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'mdb_user_id'], 'emp_org_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
