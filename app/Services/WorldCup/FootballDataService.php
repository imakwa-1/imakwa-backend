<?php

namespace App\Services\WorldCup;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FootballDataService
{
    private string $apiKey;
    private string $baseUrl;
    private string $competitionCode;

    public function __construct()
    {
        $this->apiKey = config('worldcup.football_data.api_key');
        $this->baseUrl = config('worldcup.football_data.base_url');
        $this->competitionCode = config('worldcup.football_data.competition_code');
    }

    /**
     * Get full tournament fixtures
     */
    public function getFixtures(): array
    {
        try {
            $response = $this->call("competitions/{$this->competitionCode}/matches");
            $this->logSuccess('fixtures');
            
            return app(ResponseTransformer::class)->transform($response);
        } catch (\Exception $e) {
            return $this->handleError('fixtures', $e);
        }
    }

    /**
     * Get upcoming N matches
     */
    public function getUpcoming(int $limit = 10): array
    {
        try {
            // Get fixtures and filter to scheduled matches
            $fixtures = $this->getFixtures();
            
            $upcoming = collect($fixtures['data']['matches'] ?? [])
                ->filter(fn($match) => $match['status'] === 'scheduled')
                ->sortBy(fn($match) => $match['kickoff_time'])
                ->take($limit)
                ->values()
                ->toArray();

            return [
                'success' => true,
                'data' => [
                    'matches' => $upcoming,
                    'last_updated' => now()->toIso8601String(),
                    'is_mock_data' => false,
                ],
            ];
        } catch (\Exception $e) {
            return $this->handleError('upcoming', $e);
        }
    }

    /**
     * Get live scores (Phase 2)
     */
    public function getLiveScores(): array
    {
        try {
            $response = $this->call("competitions/{$this->competitionCode}/matches?status=LIVE");
            $this->logSuccess('live_scores');
            
            // Update last_updated timestamp for freshness indicator
            Cache::put('worldcup_last_updated', now()->toIso8601String(), 3600);
            
            return app(ResponseTransformer::class)->transform($response);
        } catch (\Exception $e) {
            return $this->handleError('live_scores', $e);
        }
    }

    /**
     * Make API call to Football-Data.org
     */
    private function call(string $endpoint): array
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiKey,
        ])->timeout(10)->get($url);

        if (!$response->successful()) {
            throw new \Exception("API call failed: {$response->status()} - {$response->body()}");
        }

        // Log successful API call
        Cache::put('worldcup_last_api_call', now()->toIso8601String(), 3600);
        $this->incrementRequestLog();

        return $response->json();
    }

    /**
     * Increment hourly request counter for rate limit monitoring
     */
    private function incrementRequestLog(): void
    {
        $key = 'worldcup_api_requests_' . now()->format('Y-m-d-H');
        $current = (int) Cache::get($key, 0);
        
        Cache::put($key, $current + 1, now()->addHours(25)->diffInSeconds());
        
        // Check if approaching rate limit
        $warning = config('worldcup.rate_limit.requests_per_hour_warning');
        if ($current + 1 >= $warning) {
            Cache::put('worldcup_quota_warning', true, 3600);
            
            Log::channel('worldcup')->warning('Approaching API rate limit', [
                'requests_this_hour' => $current + 1,
                'warning_threshold' => $warning,
            ]);
        } else {
            Cache::put('worldcup_quota_warning', false, 3600);
        }
    }

    /**
     * Get current hour's request count
     */
    public function getRequestCount(): int
    {
        $key = 'worldcup_api_requests_' . now()->format('Y-m-d-H');
        return (int) Cache::get($key, 0);
    }

    /**
     * Calculate cache hit rate
     */
    public function getCacheHitRate(): float
    {
        $requests = $this->getRequestCount();
        $totalRequests = (int) Cache::get('worldcup_total_requests_' . now()->format('Y-m-d-H'), 0);
        
        if ($totalRequests === 0) {
            return 1.0;
        }
        
        return round(($totalRequests - $requests) / $totalRequests, 2);
    }

    /**
     * Log successful API operation
     */
    private function logSuccess(string $endpoint): void
    {
        Log::channel('worldcup')->info("API call successful", [
            'endpoint' => $endpoint,
            'timestamp' => now()->toIso8601String(),
            'requests_this_hour' => $this->getRequestCount(),
        ]);
    }

    /**
     * Handle API errors gracefully with fallbacks
     */
    private function handleError(string $endpoint, \Exception $e): array
    {
        Log::channel('worldcup')->error("API call failed", [
            'endpoint' => $endpoint,
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Try to return stale cache as fallback
        $cacheKey = "worldcup_{$endpoint}_backup";
        $backup = Cache::get($cacheKey);
        
        if ($backup) {
            Log::channel('worldcup')->info("Serving stale cache for {$endpoint}");
            return $backup;
        }

        // Last resort: empty response
        return [
            'success' => false,
            'data' => [
                'matches' => [],
                'last_updated' => now()->toIso8601String(),
                'is_mock_data' => false,
                'error' => 'Unable to fetch data. Please try again later.',
            ],
        ];
    }
}
