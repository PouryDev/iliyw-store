<?php

namespace App\Services\Telegram;

use Exception;
use GuzzleHttp\Client as Request;
use Throwable;

class Client
{
    private string $token;

    private string $url;

    private Request $client;

    private const METHOD_SEND_MESSAGE = 'sendMessage';

    public function __construct()
    {
        $this->token = config('telegram.token');
        $this->url = config('telegram.url');
        $this->client = new Request;
    }

    public function sendMessage(int $chatID, string $message): bool
    {
        $params = [
            'chat_id' => $chatID,
            'text' => $message,
        ];

        try {
            $response = $this->client->post($this->url, [
                'form_params' => [
                    'bot_token' => $this->token,
                    'method' => self::METHOD_SEND_MESSAGE,
                    'args' => json_encode($params),
                ],
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
