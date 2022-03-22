<?php

declare(strict_types = 1);

namespace App\Services\WeatherMonitoring;

use App\Services\ApiClient\ApiClient;
use App\Services\ApiClient\Exceptions\RequestApiException;
use App\Services\ApiClient\Exceptions\UnknownApiException;
use App\Services\ApiClient\Exceptions\WeatherApiException;
use App\Services\WeatherMonitoring\DTO\ForecastDTO;
use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

class FirstForecastService implements WeatherServiceInterface
{
    private const URI = 'weather';

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @param string $apiKey
     * @param ApiClient $client
     */
    public function __construct(string $apiKey, ApiClient $client)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
    }

    /**
     * @param string $city
     * @return WeatherForecastDTO
     * @throws WeatherApiException
     */
    public function getForecast(string $city): WeatherForecastDTO
    {
        try {
            $response = $this->client->request('GET', self::URI, [
                'query' => [
                    'appid' => $this->apiKey,
                    'q' => $city,
                    'units' => 'metric'
                ]
            ]);
        } catch (RequestApiException | UnknownApiException $e) {
            throw new WeatherApiException('Failed to send sms');
        }

        $temp = (float)$response['main']['temp'];

        return new ForecastDTO($city, $temp);
    }
}