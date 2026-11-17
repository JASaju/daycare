<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("health_records", function (Blueprint $table) {
            $table->id();
            $table->foreignId("child_id")->constrained()->cascadeOnDelete();
            $table->string("blood_group")->nullable();
            $table->text("allergies")->nullable();
            $table->text("medications")->nullable();
            $table->text("medical_conditions")->nullable();
            $table->string("doctor_name")->nullable();
            $table->string("doctor_phone")->nullable();
            $table->text("immunization_notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("health_records");
    }
};
