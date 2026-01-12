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
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id(); // PRIMARY + AUTO INCREMENT + UNIQUE

            // Plain numeric references (no foreign keys)
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();

            $table->enum('status', [
                'placed',
                'confirmed',
                'packed',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('placed');

            $table->text('note')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
