<?php

declare(strict_types = 1);

namespace App\Services\AppContainer;

use App\Services\Notification\NotificationInterface;
use App\Services\WeatherMonitoring\WeatherServiceInterface;
use Monolog\Logger;
use Spatie\Valuestore\Valuestore;

class CurrentService implements AppContainerInterface
{
    private const PROCESS_FINISH_TIME = 'finish_time';
    /**
     * @var WeatherServiceInterface
     */
    private $service;
    /**
     * @var string
     */
    private $city;
    private $notification;
    private $valuestore;
    private $repeat;
    private $timeOut;
    private $logger;

    public function __construct(
        WeatherServiceInterface $service,
        NotificationInterface $notification,
        Valuestore $valuestore,
        Logger $logger,
        string $city,
        int $repeat,
        int $timeOut
    ) {
        $this->service = $service;
        $this->city = $city;
        $this->notification = $notification;
        $this->valuestore = $valuestore;
        $this->repeat = $repeat;
        $this->timeOut = $timeOut;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function handler(): string
    {
        if ($this->isProcessFinish()) {
            $this->runCurrentApplication();
        } else {
            return $this->getStatus();
        }

        return 'success';
    }

    private function isProcessFinish(): bool
    {
        $time = $this->valuestore->get(self::PROCESS_FINISH_TIME);

        if (null === $time) {
            return true;
        }

        return (time() > $time);
    }

    public function runCurrentApplication(): void
    {
        $time = time();
        $time += ($this->timeOut * 60) * $this->repeat;
        $this->valuestore->put(self::PROCESS_FINISH_TIME, $time);

        for ($i = 0; $i < $this->repeat; $i++) {
            $this->logger->info('CurrentApplication->sent->' . $i);
            $response = $this->service->getForecast($this->city);
            $this->notification->send($response);
            sleep($this->timeOut * 60);
        }
    }

    private function getStatus(): string
    {
        $time = $this->valuestore->get(self::PROCESS_FINISH_TIME);

        return 'Process started and finished after : ' . date('y-m-d H:i:s', $time);
    }
}
