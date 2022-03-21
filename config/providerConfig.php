<?php

declare(strict_types = 1);

use App\Kernel;
use App\Services\ApiClient\ApiClient;
use App\Services\AppContainer\CurrentService;
use App\Services\Messages\MessageService;
use App\Services\WeatherMonitoring\FirstForecastService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => function () {
        $log = new Logger('fileLogger');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/log.log', Logger::DEBUG));

        return $log;
    },

    Kernel::class => DI\autowire(),
    ClientInterface::class => DI\create(Client::class),

    FirstForecastService::class => function (ContainerInterface $c) {
        $baseUrl = DI\env('WEATHER_URL', 'https://api.openweathermap.org/data/2.5/');
        $apiKey = (string)DI\env('WEATHER_URL', 'c711ae6d6489d9386270c874a35dd8fe');
        $client = new ApiClient(
            new Client([
                           'base_uri' => $baseUrl,
                           RequestOptions::TIMEOUT => 3,
                       ]),
            $c->get(LoggerInterface::class)
        );

        return new FirstForecastService($apiKey, $client);
    },

    SmsNotification::class => function (ContainerInterface $c) {
        $client = $c->get(ClientInterface::class);
        $messageService = $c->get(MessageService::class);
        $from = (string)DI\env('SMS_FROM', 'Alex');
        $to = (string)DI\env('SMS_TO', 'Alex');
        $apiKey = (string)DI\env('SMS_API_KEY', '5c5d5e28e4b0bae5f4accfec');//TODO remove default value
        $appSecret = (string)DI\env('SMS_API_SECRET', 'MGkNfqGud0');// TODO remove default value

        return new SmsNotification($client, $messageService, $apiKey, $appSecret, $from, $to);
    },

    CurrentService::class => function (ContainerInterface $c) {
        $city = (string)DI\env('CITY', 'Thessaloniki');

        return new CurrentService($c->get(FirstForecastService::class), $city);
    },

    MessageService::class => function () {
        $temp = (float)DI\env('CRITICAL_TEMP', 20);
        return new MessageService($temp);
    },
];