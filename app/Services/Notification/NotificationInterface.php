<?php

declare(strict_types = 1);

namespace App\Services\Notification;

use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

interface NotificationInterface
{
    public function send(WeatherForecastDTO $forecastDTO): void;
}

