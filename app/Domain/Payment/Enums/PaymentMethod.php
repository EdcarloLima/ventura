<?php

namespace App\Domain\Payment\Enums;

class PaymentMethod
{
    public const PIX = 'Pix';
    public const CREDITO = 'Crédito';
    public const DEBITO = 'Débito';
    public const DINHEIRO = 'Dinheiro';

    /**
     * Retorna todos os métodos de pagamento disponíveis.
     */
    public static function all(): array
    {
        return [
            self::PIX,
            self::CREDITO,
            self::DEBITO,
            self::DINHEIRO,
        ];
    }

    /**
     * Verifica se um método de pagamento é válido.
     */
    public static function isValid(string $method): bool
    {
        return in_array($method, self::all(), true);
    }

    /**
     * Verifica se o método requer integração com gateway.
     */
    public static function requiresGateway(string $method): bool
    {
        return in_array($method, [
            self::PIX,
            self::CREDITO,
            self::DEBITO,
        ], true);
    }
}
