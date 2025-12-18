<?php

namespace App\Domain\Vehicle\Services;

class PlateGeneratorService
{
    /**
     * Gera uma placa aleatória no formato (ABC1234)
     */
    public function generate(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        $plate = '';
        
        for ($i = 0; $i < 3; $i++) {
            $plate .= $letters[random_int(0, 25)];
        }
        
        for ($i = 0; $i < 4; $i++) {
            $plate .= $numbers[random_int(0, 9)];
        }

        return $plate;
    }
}
