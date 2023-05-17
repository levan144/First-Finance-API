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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->enum('recipient_type', ['company', 'individual']);
            $table->string('recipient_name');
            $table->string('sender_iban');
            $table->string('recipient_iban');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('to_currency_id')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('received_amount', 15, 2)->nullable();
            $table->decimal('fee', 15, 2)->nullable();
            $table->enum('type', ['transfer', 'deposit', 'exchange']);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
    
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('to_currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
