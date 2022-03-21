<?php

declare(strict_types = 1);

namespace App\Services\WeatherMonitoring\DTO;

class ForecastDTO implements WeatherForecastDTO
{
    /**
     * @var string
     */
    private $city;
    /**
     * @var float
     */
    private $temp;

    /**
     * @param string $city
     * @param float $temp
     */
    public function __construct(string $city, float $temp)
    {
        $this->city = $city;
        $this->temp = $temp;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return float
     */
    public function getTemp(): float
    {
        return $this->temp;
    }
}
