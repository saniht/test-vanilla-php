<?php

declare(strict_types = 1);

namespace App\Services\WeatherMonitoring\DTO;

interface WeatherForecastDTO
{

    public function getCity(): string;

    public function getTemp(): float;
}