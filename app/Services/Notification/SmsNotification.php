<?php

declare(strict_types = 1);

namespace App\Services\Notification;

use App\Services\ApiClient\ApiClient;
use App\Services\ApiClient\Exceptions\RequestApiException;
use App\Services\ApiClient\Exceptions\SmsApiException;
use App\Services\ApiClient\Exceptions\UnknownApiException;
use App\Services\Messages\MessageInterface;
use App\Services\WeatherMonitoring\DTO\WeatherForecastDTO;

class SmsNotification implements NotificationInterface
{
    private const URI = 'sms';
    /**
     * @var SmsNotificationSecurity
     */
    private $security;
    /**
     * @var ApiClient
     */
    private $client;
    /**
     * @var MessageInterface
     */
    private $message;
    /**
     * @var string
     */
    private $from;
    /**
     * @var string
     */
    private $to;

    /**
     * @param ApiClient $client
     * @param MessageInterface $message
     * @param SmsNotificationSecurity $security
     * @param string $from
     * @param string $to
     */
    public function __construct(
        ApiClient $client,
        MessageInterface $message,
        SmsNotificationSecurity $security,
        string $from,
        string $to
    ) {
        $this->security = $security;
        $this->client = $client;
        $this->from = $from;
        $this->to = $to;
        $this->message = $message;
    }

    /**
     * @param WeatherForecastDTO $forecastDTO
     * @throws SmsApiException
     */
    public function send(WeatherForecastDTO $forecastDTO): void
    {
        $message = $this->message->getMessage($forecastDTO);
        $token = $this->security->getToken();

        try {
            $this->client->request('POST', self::URI, [
                [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'body' => $message,
                    'to' => $this->to,
                    'from' => $this->from
                ]
            ]);
        } catch (RequestApiException | UnknownApiException $e) {
            throw new SmsApiException('Failed to send sms');
        }
    }
}