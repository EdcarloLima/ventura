<?php

namespace App\Domain\Payment\DataTransferObjects;

readonly class PaymentRegisterDto
{
    public function __construct(
        public string $pixPayload,   // O código "Copia e Cola"
        public string $qrCodeBase64, // A imagem
        public string $gatewayId     // ID do Mercado Pago (para o webhook achar depois)
    ) {}
}