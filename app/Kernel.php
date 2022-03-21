<?php

declare(strict_types = 1);

namespace App;

use App\Services\AppContainer\AppContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Kernel
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AppContainerInterface $container
     * @return string
     */
    public function handle(AppContainerInterface $container): string
    {
//        try {
            $response = $container->handler();

            $this->logger->info('test');
//        } catch (Throwable $e) {
//            //Top level handling of all exceptions in an application
//            $response = $e->getMessage();
//        }

        return $response;
    }
}