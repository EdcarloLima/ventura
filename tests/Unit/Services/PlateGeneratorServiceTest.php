<?php

namespace Tests\Unit\Services;

use App\Domain\Vehicle\Services\PlateGeneratorService;
use Tests\TestCase;

class PlateGeneratorServiceTest extends TestCase
{
    private PlateGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlateGeneratorService();
    }

    /** @test */
    public function it_generates_plate_with_correct_format()
    {
        $plate = $this->service->generate();

        $this->assertEquals(7, strlen($plate));

        $this->assertMatchesRegularExpression('/^[A-Z]{3}[0-9]{4}$/', $plate);
    }

    /** @test */
    public function it_generates_different_plates()
    {
        $plates = [];
        
        for ($i = 0; $i < 100; $i++) {
            $plates[] = $this->service->generate();
        }

        $uniquePlates = array_unique($plates);

        $this->assertGreaterThan(95, count($uniquePlates));
    }

    /** @test */
    public function it_generates_only_uppercase_letters()
    {
        $plate = $this->service->generate();
        
        $letters = substr($plate, 0, 3);
        
        $this->assertEquals($letters, strtoupper($letters));
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $letters);
    }

    /** @test */
    public function it_generates_only_digits_for_numbers()
    {
        $plate = $this->service->generate();
        
        $numbers = substr($plate, 3, 4);
        
        $this->assertMatchesRegularExpression('/^[0-9]{4}$/', $numbers);
    }
}
