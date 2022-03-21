<?php

declare(strict_types = 1);

use App\Kernel;
use App\Services\ApiClient\ApiClient;
use App\Services\AppContainer\CurrentService;
use App\Services\Messages\MessageInterface;
use App\Services\Messages\MessageService;
use App\Services\Notification\NotificationInterface;
use App\Services\Notification\SecurityInterface;
use App\Services\Notification\SmsNotification;
use App\Services\Notification\SmsNotificationSecurity;
use App\Services\WeatherMonitoring\FirstForecastService;
use App\Services\WeatherMonitoring\WeatherService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Spatie\Valuestore\Valuestore;

return [
    'city' => \DI\env('CITY', 'Thessaloniki'),
    'critical_temp' => (float) \DI\env('CRITICAL_TEMP', 20),
    'weather_url' => 'https://api.openweathermap.org/data/2.5/',
    'weather_api_key' => 'c711ae6d6489d9386270c874a35dd8fe',
    'sms_url' => (string)\DI\env('SMS_URL', 'https://connect.routee.net/'),
    'sms_from' => (string)\DI\env('SMS_FROM', 'Alex'),
    'sms_to' => (string)\DI\env('SMS_TO', '+30 6911111111'),
    'sms_auth_url' => (string)\DI\env('SMS_AUTH_URL', 'https://auth.routee.net/'),
    'sms_api_key' => (string)\DI\env('SMS_API_KEY', 'secret'),
    'sms_api_secret' => (string)\DI\env('SMS_API_SECRET', 'secret'),
    'app_repeat' => (int)\DI\env('APP_REPEAT', 10),
    'app_repeat_timeout' => (int)\DI\env('APP_REPEAT_TIMEOUT', 10),

    LoggerInterface::class => function () {
        $log = new Logger('fileLogger');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/log.log', Logger::DEBUG));

        return $log;
    },

    MessageInterface::class => function (ContainerInterface $c) {
        $temp = $c->get('critical_temp');

        return new MessageService($temp);
    },

    Kernel::class => DI\autowire(),

    ClientInterface::class => DI\create(Client::class),

    WeatherService::class => function (ContainerInterface $c) {
        $baseUrl = $c->get('weather_url');
        $apiKey = $c->get('weather_api_key');

        $client = new ApiClient(
            new Client([
                           'base_uri' => $baseUrl,
                           RequestOptions::TIMEOUT => 3,
                       ]),
            $c->get(LoggerInterface::class)
        );

        return new FirstForecastService($apiKey, $client);
    },

    NotificationInterface::class => function (ContainerInterface $c) {
        $baseUrl = $c->get('sms_url');

        $client = new ApiClient(
            new Client([
                           'base_uri' => $baseUrl,
                           RequestOptions::TIMEOUT => 3,
                           RequestOptions::HEADERS => [
                               "Content-type" => "application/json"
                           ],
                       ]),
            $c->get(LoggerInterface::class)
        );
        $messageService = $c->get(MessageInterface::class);
        $security = $c->get(SecurityInterface::class);
        $from = $c->get('sms_from');
        $to = $c->get('sms_to');

        return new SmsNotification($client, $messageService, $security, $from, $to);
    },

    SecurityInterface::class => function (ContainerInterface $c) {
        $baseUrl = $c->get('sms_auth_url');
        $apiKey = $c->get('sms_api_key');
        $appSecret = $c->get('sms_api_secret');

        $client = new ApiClient(
            new Client([
                           'base_uri' => $baseUrl,
                           RequestOptions::TIMEOUT => 3,
                           RequestOptions::HEADERS => [
                               'grant_type' => 'client_credentials',
                               'Content-type' => 'application/x-www-form-urlencoded'
                           ],
                       ]),
            $c->get(LoggerInterface::class)
        );

        $valueStore = $c->get(Valuestore::class);

        return new SmsNotificationSecurity($client, $valueStore, $apiKey, $appSecret);
    },

    Valuestore::class => function () {
        return Valuestore::make(__DIR__ . '/../storage/cache/cache.json');
    },

    CurrentService::class => function (ContainerInterface $c) {
        return new CurrentService(
            $c->get(WeatherService::class),
            $c->get(NotificationInterface::class),
            $c->get(Valuestore::class),
            $c->get(LoggerInterface::class),
            $c->get('city'),
            $c->get('app_repeat'),
            $c->get('app_repeat_timeout')
        );
    },
];