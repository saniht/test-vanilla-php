<?php
declare(strict_types = 1);

use App\Services\ApiClient\ApiClient;
use App\Services\Messages\MessageInterface;
use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;
use App\Services\WeatherMonitoring\NotificationInterface;

class SmsNotification implements NotificationInterface {

    private const URI = "https://connect.routee.net/sms";
    private const AUTH_URI = "https://auth.routee.net/oauth/token";

    /**
     * @var string
     */
    private $apiKey;
    private $appSecret;
    /**
     * @var ApiClient
     */
    private $client;
    private $message;


    private $from;
    private $to;

    public function __construct( ApiClient $client, MessageInterface $message ,string $apiKey,string $appSecret, string $from, string $to)
    {
        $this->apiKey = $apiKey;
        $this->appSecret = $appSecret;
        $this->client = $client;
        $this->from = $from;
        $this->to = $to;
        $this->message = $message;
    }




    public function send(WeatherForecastDTO $forecastDTO):void
    {
        $message = $this->message->getMessage($forecastDTO);

        $this->client->request('POST', self::URI,[
            'json' => [
                'body' => $message,
                'to' => $this->to,
                'from' => $this->from
            ]]);
    }



}