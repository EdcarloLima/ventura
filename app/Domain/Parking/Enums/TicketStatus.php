<?php

namespace App\Domain\Parking\Enums;

class TicketStatus
{
    public const ABERTO = 'Aberto';
    public const PAGAMENTO_PENDENTE = 'Pagamento pendente';
    public const PAGO = 'Pago';
    public const CONCLUIDO = 'Concluído';
    public const CANCELADO = 'Cancelado';

    /**
     * Retorna todos os status disponíveis.
     */
    public static function all(): array
    {
        return [
            self::ABERTO,
            self::PAGAMENTO_PENDENTE,
            self::PAGO,
            self::CONCLUIDO,
            self::CANCELADO,
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
     * Retorna os status que permitem pagamento.
     */
    public static function canPay(): array
    {
        return [
            self::ABERTO,
            self::PAGAMENTO_PENDENTE,
        ];
    }

    /**
     * Retorna os status finais (não podem ser alterados).
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, [
            self::CONCLUIDO,
            self::CANCELADO,
        ], true);
    }
}
