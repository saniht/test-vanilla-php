<?php

declare(strict_types = 1);

namespace App\Services\ApiClient;

use App\Services\ApiClient\Exceptions\RequestApiException;
use App\Services\ApiClient\Exceptions\UnknownApiException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ApiClient constructor.
     * @param ClientInterface $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return mixed
     * @throws RequestApiException
     * @throws UnknownApiException
     */
    public function request(string $method, string $uri = '', array $options = [])
    {
        try {
            $this->logger->debug(sprintf('%s %s is sending with params %s', $method, $uri, json_encode($options)));
            $response = $this->httpClient->request($method, $uri, $options);
            return json_decode((string)$response->getBody(), true);
        } catch (ClientException $e) {
            $message = sprintf('Client error during request %s %s: %s', $method, $uri, $e->getMessage());
            $this->logger->error($message);

            $body = $e->getResponse() ? json_decode((string)$e->getResponse()->getBody(), true) : false;
            if (false === $body || ! isset($body['message'])) {
                throw new UnknownApiException($message, $e->getCode(), $e);
            }
            throw new RequestApiException($body['message']);
        } catch (GuzzleException $e) {
            $message = sprintf('%s during %s %s request', $e->getMessage(), $method, $uri);
            $this->logger->warning($message);
            throw new UnknownApiException($message, (int)$e->getCode(), $e);
        }
    }
}