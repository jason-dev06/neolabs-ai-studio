<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiVideoClient
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) config('ai.providers.gemini.key');
    }

    /**
     * Submit a video generation request.
     *
     * @return string The operation name for polling.
     *
     * @throws RequestException
     */
    public function generate(string $model, string $prompt, int $duration, string $aspectRatio): string
    {
        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])->post(self::BASE_URL."/models/{$model}:predictLongRunning", [
            'instances' => [
                ['prompt' => $prompt],
            ],
            'parameters' => [
                'durationSeconds' => (int) $duration,
                'aspectRatio' => $aspectRatio,
                'resolution' => '720p',
            ],
        ])->throw();

        $name = $response->json('name');

        if (! $name) {
            throw new RuntimeException('Gemini API did not return an operation name.');
        }

        return $name;
    }

    /**
     * Poll a single time for operation status.
     *
     * @return array{done: bool, response?: array}
     *
     * @throws RequestException
     */
    public function poll(string $operationName): array
    {
        return Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])->get(self::BASE_URL."/{$operationName}")
            ->throw()
            ->json();
    }

    /**
     * Poll until the operation completes or times out.
     *
     * @return array The completed operation response.
     *
     * @throws RuntimeException
     * @throws RequestException
     */
    public function pollUntilDone(string $operationName, int $timeoutSeconds = 540, int $intervalSeconds = 10): array
    {
        $start = time();

        while (true) {
            $result = $this->poll($operationName);

            if (! empty($result['done'])) {
                if (isset($result['error'])) {
                    throw new RuntimeException('Gemini video generation failed: '.($result['error']['message'] ?? 'Unknown error'));
                }

                return $result;
            }

            if ((time() - $start) >= $timeoutSeconds) {
                throw new RuntimeException("Gemini video generation timed out after {$timeoutSeconds} seconds.");
            }

            sleep($intervalSeconds);
        }
    }

    /**
     * Download video content from the given URI.
     *
     * @throws RequestException
     */
    public function downloadVideo(string $videoUri): string
    {
        return Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])->get($videoUri)
            ->throw()
            ->body();
    }
}
