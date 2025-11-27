<?php

namespace App\Domain\Vehicle\Enums;

class VehicleType
{
    public const CARRO = 'Carro';
    public const CAMINHAO = 'Caminhão';
    public const CAMINHONETE = 'Caminhonete';
    public const CICLOMOTOR = 'Ciclomotor';
    public const MOTO = 'Moto';
    public const ONIBUS = 'Ônibus';
    public const QUADRICICLO = 'Quadricíclo';
    public const TRICICLO = 'Triciclo';

    /**
     * Retorna todos os tipos de veículos disponíveis.
     */
    public static function all(): array
    {
        return [
            self::CARRO,
            self::CAMINHAO,
            self::CAMINHONETE,
            self::CICLOMOTOR,
            self::MOTO,
            self::ONIBUS,
            self::QUADRICICLO,
            self::TRICICLO,
        ];
    }

    /**
     * Verifica se um tipo de veículo é válido.
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }

    /**
     * Verifica se o veículo é de duas rodas.
     */
    public static function isTwoWheeler(string $type): bool
    {
        return in_array($type, [
            self::MOTO,
            self::CICLOMOTOR,
        ], true);
    }

    /**
     * Verifica se o veículo é de grande porte.
     */
    public static function isLargeVehicle(string $type): bool
    {
        return in_array($type, [
            self::CAMINHAO,
            self::ONIBUS,
        ], true);
    }
}
