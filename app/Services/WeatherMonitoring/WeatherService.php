<?php

declare(strict_types = 1);

namespace App\Services\WeatherMonitoring;

use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

interface WeatherService
{
    public function getForecast(string $city): WeatherForecastDTO;
}

