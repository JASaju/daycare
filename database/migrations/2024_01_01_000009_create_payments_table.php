<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("payments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("invoice_id")->constrained()->cascadeOnDelete();
            $table->decimal("amount", 10, 2);
            $table->date("paid_date");
            $table->enum("method", ["cash", "bank_transfer", "card", "mobile_banking"])->default("cash");
            $table->string("reference")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payments");
    }
};
