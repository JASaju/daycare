<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("invoices", function (Blueprint $table) {
            $table->id();
            $table->foreignId("employee_id")->constrained()->cascadeOnDelete();
            $table->foreignId("child_id")->nullable()->constrained()->nullOnDelete();
            $table->string("month");
            $table->integer("year");
            $table->decimal("amount", 10, 2);
            $table->date("due_date")->nullable();
            $table->enum("status", ["unpaid", "partial", "paid", "overdue"])->default("unpaid");
            $table->text("notes")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("invoices");
    }
};
