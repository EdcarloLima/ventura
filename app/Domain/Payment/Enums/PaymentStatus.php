<?php

namespace App\Domain\Payment\Enums;

class PaymentStatus
{
    public const PENDENTE = 'Pendente';
    public const APROVADO = 'Aprovado';
    public const ERRO = 'Erro';

    /**
     * Retorna todos os status disponíveis.
     */
    public static function all(): array
    {
        return [
            self::PENDENTE,
            self::APROVADO,
            self::ERRO,
        ];
    }

    /**
     * Verifica se um status é válido.
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }

    /**
     * Verifica se o pagamento foi bem-sucedido.
     */
    public static function isSuccessful(string $status): bool
    {
        return $status === self::APROVADO;
    }

    /**
     * Verifica se o pagamento pode ser reprocessado.
     */
    public static function canRetry(string $status): bool
    {
        return in_array($status, [
            self::PENDENTE,
            self::ERRO,
        ], true);
    }
}
