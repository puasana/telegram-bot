<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class ChatGPTService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;
    protected $maxRetries;
    protected $retryDelay;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('OPENAI_API_KEY');
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions'; // Correct endpoint for chat models
        $this->maxRetries = 5; // Maximum number of retries
        $this->retryDelay = 1000; // Initial delay in milliseconds
    }

    public function sendMessage($message)
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                $response = $this->client->post($this->apiUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'gpt-3.5-turbo-16k-0613', // Use the chat model you have access to
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                            ['role' => 'user', 'content' => $message],
                        ],
                    ],
                ]);

                $body = json_decode($response->getBody(), true);
                return $body['choices'][0]['message']['content'] ?? 'Sorry, I could not understand your request.';
            } catch (RequestException $e) {
                if ($e->getResponse() && $e->getResponse()->getStatusCode() == 429) {
                    $attempts++;
                    Log::warning("429 Too Many Requests. Attempt $attempts of $this->maxRetries. Retrying in $this->retryDelay ms.");
                    usleep($this->retryDelay * 1000); // Delay before retrying
                    $this->retryDelay *= 2; // Exponential backoff
                } else {
                    Log::error('Request Exception: ' . $e->getMessage());
                    return 'Error: ' . $e->getMessage();
                }
            } catch (ConnectException $e) {
                Log::error('Network Error: ' . $e->getMessage());
                return 'Network Error: ' . $e->getMessage();
            }
        }

        Log::error('Too many requests. Please try again later.');
        return 'Error: Too many requests. Please try again later.';
    }
}
