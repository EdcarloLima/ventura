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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->char('plate', 7)->unique()->index();
            $table->enum('type', [
                'Carro',
                'Caminhão',
                'Caminhonete',
                'Ciclomotor',
                'Moto',
                'Ônibus',
                'Quadricíclo',
                'Triciclo'
            ])->default('Carro');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
