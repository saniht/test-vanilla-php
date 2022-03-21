<?php

declare(strict_types = 1);

namespace App\Services\Messages;

use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

interface MessageInterface
{
    public function getMessage(WeatherForecastDTO $forecastDTO): string;
}

