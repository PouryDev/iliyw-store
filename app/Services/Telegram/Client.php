<?php

namespace App\Services\Telegram;

use Exception;
use GuzzleHttp\Client as Request;
use Throwable;

class Client
{
    private string $token;

    private string $proxyUrl;

    private Request $client;

    private const METHOD_SEND_MESSAGE = 'sendMessage';

    public function __construct()
    {
        $this->token = config('telegram.token');
        $this->proxyUrl = config('telegram.proxy_url', 'https://snowy-tree-5c79.pk74ever.workers.dev');
        $this->client = new Request;
    }

    public function sendMessage(int $chatID, string $message): bool
    {
        $params = [
            'chat_id' => $chatID,
            'text' => $message,
        ];

        try {
            // Build URL: {proxy_url}/bot{token}/sendMessage
            $url = rtrim($this->proxyUrl, '/') . '/bot' . $this->token . '/' . self::METHOD_SEND_MESSAGE;

            // Send GET request with query parameters
            $response = $this->client->get($url, [
                'query' => $params,
            ]);

            // Check if request was successful (status code 200)
            if ($response->getStatusCode() === 200) {
                return true;
            }

            logger()->error('[TELEGRAM] Unexpected status code', [
                'status_code' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
            ]);
            return false;
        } catch (Exception|Throwable $e) {
            report($e);
            $errorMessage = '[TELEGRAM] Failed to send message using telegram APIs';

            if (!($e instanceof Exception)) {
                $errorMessage = '[TELEGRAM] Unexpected error while sending message using Telegram APIs';
            }

            logger()->error($errorMessage, [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
            return false;
        }
    }
}
