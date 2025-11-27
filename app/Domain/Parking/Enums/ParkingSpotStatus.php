<?php

namespace App\Domain\Parking\Enums;

class ParkingSpotStatus
{
    public const DISPONIVEL = 'Disponível';
    public const OCUPADO = 'Ocupado';
    public const MANUTENCAO = 'Manutenção';

    /**
     * Retorna todos os status disponíveis.
     */
    public static function all(): array
    {
        return [
            self::DISPONIVEL,
            self::OCUPADO,
            self::MANUTENCAO,
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
     * Verifica se a vaga pode ser ocupada.
     */
    public static function canOccupy(string $status): bool
    {
        return $status === self::DISPONIVEL;
    }
}
