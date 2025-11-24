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
            $table->id();
            $table->uuid('ticket_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['Pix', 'Crédito', 'Débito', 'Dinheiro']);
            $table->string('gateway_transaction_id')->nullable();
            $table->enum('status', ['Pendente', 'Aprovado', 'Erro'])->default('Pendente');
            $table->timestamps();
            $table->softDeletes();
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
