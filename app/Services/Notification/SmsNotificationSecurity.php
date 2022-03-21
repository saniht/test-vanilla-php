<?php

declare(strict_types = 1);

namespace App\Services\Notification;

use App\Services\ApiClient\ApiClient;
use App\Services\ApiClient\Exceptions\RequestApiException;
use App\Services\ApiClient\Exceptions\SmsApiException;
use App\Services\ApiClient\Exceptions\UnknownApiException;
use Spatie\Valuestore\Valuestore;

class SmsNotificationSecurity implements SecurityInterface
{
    private const URI = 'oauth/token';
    private const TOKEN = 'sms_token';
    private const TOKEN_LIFE_TIME = 'sms_token_life_time';

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $appSecret;
    /**
     * @var ApiClient
     */
    private $client;
    /**
     * @var Valuestore
     */
    private $valueStore;

    /**
     * @param ApiClient $client
     * @param Valuestore $valueStore
     * @param string $apiKey
     * @param string $appSecret
     */
    public function __construct(ApiClient $client, Valuestore $valueStore, string $apiKey, string $appSecret)
    {
        $this->apiKey = $apiKey;
        $this->appSecret = $appSecret;
        $this->client = $client;
        $this->valueStore = $valueStore;
    }

    public function getToken(): string
    {
        if ($this->isTokenValid()) {
            return $this->valueStore->get(self::TOKEN);
        }

        return $this->getNewToken();
    }

    /**
     * @return bool
     */
    private function isTokenValid(): bool
    {
        if (! $this->isTokenExists()) {
            return false;
        }
        $tokenLifeTime = $this->valueStore->get(self::TOKEN_LIFE_TIME, 0);

        if (0 === $tokenLifeTime) {
            return false;
        }

        if (time() > $tokenLifeTime) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @throws SmsApiException
     */
    private function getNewToken(): string
    {
        try{
            $response = $this->client->request(
                'POST',
                self::URI,
                [
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey . ":" . $this->appSecret),
                ]
            );
        }catch (RequestApiException| UnknownApiException $e){
            throw new SmsApiException('Failed to get auth token');
        }


        if (empty($response['access_token']) || empty($response['expires_in'])) {
            throw new SmsApiException('Incorrect response format');
        }

        $token = (string)$response['access_token'];
        $lifeTime = (int)$response['expires_in'];

        $this->updateTokenData($token, $lifeTime);

        return $token;
    }

    /**
     * @return bool
     */
    private function isTokenExists(): bool
    {
        $token = $this->valueStore->get(self::TOKEN);

        if (null === $token) {
            return false;
        }

        return true;
    }

    /**
     * @param string $token
     * @param int $lifeTime
     */
    private function updateTokenData(string $token, int $lifeTime)
    {
        $this->valueStore->put(self::TOKEN);
        $lifeTime = time() + ($lifeTime - 1);
        $this->valueStore->put(self::TOKEN_LIFE_TIME, $lifeTime);
    }
}