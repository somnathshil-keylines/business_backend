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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // PRIMARY + AUTO INCREMENT + UNIQUE

            // Plain reference (no foreign key)
            $table->unsignedBigInteger('order_id')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
