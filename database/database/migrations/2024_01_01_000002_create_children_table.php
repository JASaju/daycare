<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("children", function (Blueprint $table) {
            $table->id();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->string("name");
            $table->date("date_of_birth");
            $table->enum("gender", ["male", "female", "other"])->nullable();
            $table->string("photo")->nullable();
            $table->text("notes")->nullable();
            $table->enum("status", ["active", "inactive"])->default("active");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("children");
    }
};
