<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MNotifyService
{
    public function isConfigured(): bool
    {
        return (bool) config('mnotify.enabled')
            && trim((string) config('mnotify.api_key')) !== ''
            && trim((string) config('mnotify.sender_id')) !== '';
    }

    /**
     * @param  array<int, string>  $recipients
     * @return array<string, mixed>|null
     */
    public function sendQuickSms(array $recipients, string $message): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $recipients = array_values(array_filter(array_unique($recipients)));

        if ($recipients === []) {
            return null;
        }

        $combined = [
            'status' => 'success',
            'chunks' => 0,
            'summary' => [],
        ];

        foreach (array_chunk($recipients, 200) as $chunk) {
            $payload = [
                'recipient' => array_values($chunk),
                'sender' => substr((string) config('mnotify.sender_id'), 0, 11),
                'message' => $message,
                'is_schedule' => false,
            ];

            $response = $this->postQuickSms($payload);

            if ($response === null) {
                $combined['chunks']++;
                $combined['summary'][] = ['status' => 'success', 'unconfirmed' => true];
                continue;
            }

            $body = $response->json();

            if (! $this->accepted($response, $body)) {
                Log::warning('mNotify SMS failed', [
                    'status' => $response->status(),
                    'code' => is_array($body) ? ($body['code'] ?? null) : null,
                    'message' => is_array($body) ? ($body['message'] ?? null) : null,
                ]);

                return $combined['chunks'] > 0 ? $combined : null;
            }

            $combined['chunks']++;
            $combined['summary'][] = is_array($body) ? $body : [];
        }

        return $combined;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postQuickSms(array $payload): ?Response
    {
        $lastResponse = null;
        $connectionDropped = false;

        foreach ($this->candidateUrls('/sms/quick') as $url) {
            try {
                $response = $this->client()->post($url, $payload);

                if ($this->accepted($response, $response->json())) {
                    return $response;
                }

                $lastResponse = $response;
            } catch (ConnectionException) {
                $connectionDropped = true;
                Log::warning('mNotify connection failed', [
                    'scheme' => parse_url($url, PHP_URL_SCHEME),
                    'host' => parse_url($url, PHP_URL_HOST),
                ]);
            }
        }

        if ($lastResponse) {
            return $lastResponse;
        }

        return $connectionDropped ? null : $lastResponse;
    }

    /**
     * @return list<string>
     */
    private function candidateUrls(string $path): array
    {
        $primary = $this->endpoint($path);
        $urls = [$primary];

        if (config('mnotify.allow_http_fallback', true) && str_starts_with($primary, 'https://')) {
            $http = preg_replace('#^https://#', 'http://', $primary);
            $urls = [$http, $primary];
        }

        return array_values(array_unique($urls));
    }

    private function client()
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout(45)
            ->connectTimeout(20)
            ->withOptions([
                'http_errors' => false,
                'allow_redirects' => false,
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_FOLLOWLOCATION => 0,
                ],
            ]);
    }

    private function accepted(Response $response, mixed $payload): bool
    {
        if (is_array($payload) && ($payload['status'] ?? null) === 'success') {
            return true;
        }

        if (is_array($payload) && in_array((string) ($payload['code'] ?? ''), ['2000', '2001', '1000'], true)) {
            return true;
        }

        return $response->redirect();
    }

    private function endpoint(string $path): string
    {
        $baseUrl = rtrim((string) config('mnotify.base_url'), '/');
        $apiKey = urlencode((string) config('mnotify.api_key'));

        return $baseUrl.$path.'?key='.$apiKey;
    }
}
