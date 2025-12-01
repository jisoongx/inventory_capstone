<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected string $baseUrl;

    public function __construct()
    {
        $mode = config('paypal.mode', 'sandbox');
        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get OAuth 2.0 access token from PayPal
     */
    public function getAccessToken(): ?string
    {
        try {
            $client = new Client();

            $clientId = config("paypal.{$this->getMode()}.client_id");
            $clientSecret = config("paypal.{$this->getMode()}.client_secret");

            $response = $client->post($this->baseUrl . '/v1/oauth2/token', [
                'auth' => [$clientId, $clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['access_token'] ?? null;
        } catch (Exception $e) {
            Log::error('PayPal getAccessToken error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel a PayPal subscription
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'Canceled by user'): bool
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                Log::error('PayPal cancel subscription failed: no access token');
                return false;
            }

            $client = new Client();
            $response = $client->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'reason' => $reason
                ]
            ]);

            return $response->getStatusCode() === 204;
        } catch (Exception $e) {
            Log::error('PayPal cancel subscription error: ' . $e->getMessage());
            return false;
        }
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                Log::error("PayPal getSubscription failed: no access token for {$subscriptionId}");
                return null;
            }

            $client = new Client();
            $response = $client->get("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::warning("PayPal getSubscription non-200 response for {$subscriptionId}");
                return null;
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            Log::error('PayPal getSubscription error: ' . $e->getMessage());
            return null;
        }
    }

    private function getMode(): string
    {
        return config('paypal.mode', 'sandbox');
    }
}
