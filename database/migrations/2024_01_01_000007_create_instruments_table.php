<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("instruments", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("category")->nullable();
            $table->integer("quantity")->default(1);
            $table->enum("condition", ["new", "good", "fair", "damaged"])->default("good");
            $table->date("purchase_date")->nullable();
            $table->decimal("purchase_price", 10, 2)->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("instruments");
    }
};
