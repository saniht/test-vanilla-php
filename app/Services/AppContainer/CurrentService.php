<?php

declare(strict_types = 1);

namespace App\Services\AppContainer;

use App\Services\WeatherMonitoring\WeatherService;

class CurrentService implements AppContainerInterface
{
    /**
     * @var WeatherService
     */
    private $service;
    /**
     * @var string
     */
    private $city;

    /**
     * @param WeatherService $service
     * @param string $city
     */
    public function __construct(WeatherService $service, string $city)
    {
        $this->service = $service;
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function handler(): string
    {
        $this->service->getForecast($this->city);

        return 'ContainerService->handler()';
    }
}
