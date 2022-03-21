<?php

declare(strict_types = 1);

namespace App\Services\Messages;

use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

class MessageService implements MessageInterface
{
    /**
     * @var float
     */
    private $criticalTemp;

    /**
     * @param float $criticalTemp
     */
    public function __construct(float $criticalTemp)
    {
        $this->criticalTemp = $criticalTemp;
    }

    /**
     * @param WeatherForecastDTO $forecastDTO
     * @return string
     */
    public function getMessage(WeatherForecastDTO $forecastDTO): string
    {
        $status = $this->criticalTemp > $forecastDTO->getTemp() ? 'less' : 'more';

        return $this->prepareMessage($forecastDTO->getCity(), $status, $forecastDTO->getTemp());
    }

    /**
     * @param string $city
     * @param string $status
     * @param float $temp
     * @return string
     */
    private function prepareMessage(string $city, string $status, float $temp): string
    {
        return "{$city} : Temperature {$status} than {$this->criticalTemp} C. Actual: {$temp}";
    }
}