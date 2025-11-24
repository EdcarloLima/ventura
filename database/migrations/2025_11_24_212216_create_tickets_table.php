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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('spot_id')->constrained('parking_spots')->onDelete('cascade');
            $table->dateTime('entry_at');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('exit_at')->nullable();
            $table->enum('status', [
                'Aberto',
                'Pagamento pendente',
                'Pago',
                'Concluído',
                'Cancelado'
            ])->default('Aberto');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
