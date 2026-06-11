<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorldCup\FootballDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LiveScoreController extends Controller
{
    /**
     * Get full tournament fixtures
     * GET /api/v1/worldcup/fixtures
     */
    public function fixtures(Request $request)
    {
        $ttl = config('worldcup.cache_ttl.fixtures');
        
        // Increment total request counter for hit rate calculation
        $this->incrementTotalRequests('fixtures');
        
        return Cache::remember('worldcup_fixtures', $ttl, function () {
            // Also store as backup for fallback
            $data = app(FootballDataService::class)->getFixtures();
            Cache::put('worldcup_fixtures_backup', $data, 86400); // 24h backup
            return response()->json($data);
        });
    }

    /**
     * Get upcoming N matches
     * GET /api/v1/worldcup/upcoming
     */
    public function upcoming(Request $request)
    {
        $ttl = config('worldcup.cache_ttl.upcoming');
        $limit = config('worldcup.upcoming_limit');
        
        $this->incrementTotalRequests('upcoming');
        
        return Cache::remember('worldcup_upcoming', $ttl, function () use ($limit) {
            $data = app(FootballDataService::class)->getUpcoming($limit);
            Cache::put('worldcup_upcoming_backup', $data, 86400);
            return response()->json($data);
        });
    }

    /**
     * Get live scores
     * GET /api/v1/worldcup/live-scores
     * 
     * Phase 1: Returns mock data (controlled by admin panel)
     * Phase 2: Returns real API data (admin switches tournament mode)
     */
    public function liveScores(Request $request)
    {
        // Get TTL from config (30s during tournament, 60s pre-tournament)
        $tournamentMode = Cache::get('worldcup_tournament_mode', 'pre-tournament');
        $ttl = $tournamentMode === 'live' ? 30 : config('worldcup.cache_ttl.live_scores');
        
        $this->incrementTotalRequests('live_scores');
        
        return Cache::remember('worldcup_live_scores', $ttl, function () {
            $useMock = (bool) Cache::get('worldcup_use_mock', true);
            
            if ($useMock) {
                return response()->json($this->getMockScores());
            }
            
            $data = app(FootballDataService::class)->getLiveScores();
            Cache::put('worldcup_live_scores_backup', $data, 86400);
            return response()->json($data);
        });
    }

    /**
     * Get system status and health metrics
     * GET /api/v1/worldcup/scores-status
     */
    public function scoresStatus(Request $request)
    {
        $service = app(FootballDataService::class);
        $requestsThisHour = $service->getRequestCount();
        $warningThreshold = config('worldcup.rate_limit.requests_per_hour_warning');
        
        return response()->json([
            'status' => 'ok',
            'using_mock' => (bool) Cache::get('worldcup_use_mock', true),
            'tournament_mode' => Cache::get('worldcup_tournament_mode', 'pre-tournament'),
            'cache_hit_rate' => $service->getCacheHitRate(),
            'last_api_call' => Cache::get('worldcup_last_api_call'),
            'last_updated' => Cache::get('worldcup_last_updated'),
            'requests_this_hour' => $requestsThisHour,
            'quota_warning' => Cache::get('worldcup_quota_warning', false),
            'quota_threshold' => $warningThreshold,
        ]);
    }

    /**
     * Mock data for Phase 1 (Pre-World Cup)
     * Realistic match data structure for development/staging
     */
    private function getMockScores(): array
    {
        return [
            'success' => true,
            'data' => [
                'matches' => [
                    [
                        'id' => 'mock_001',
                        'status' => 'live',
                        'minute' => rand(1, 90),
                        'home_team' => [
                            'name' => 'Nigeria',
                            'code' => 'NGA',
                            'flag' => '🇳🇬',
                            'score' => rand(0, 3),
                        ],
                        'away_team' => [
                            'name' => 'Ghana',
                            'code' => 'GHA',
                            'flag' => '🇬🇭',
                            'score' => rand(0, 3),
                        ],
                        'competition' => 'FIFA World Cup 2026',
                        'stage' => 'Group Stage - Group A',
                        'venue' => 'MetLife Stadium, New Jersey',
                        'kickoff_time' => now()->subMinutes(rand(30, 90))->toIso8601String(),
                    ],
                    [
                        'id' => 'mock_002',
                        'status' => 'live',
                        'minute' => rand(1, 90),
                        'home_team' => [
                            'name' => 'Senegal',
                            'code' => 'SEN',
                            'flag' => '🇸🇳',
                            'score' => rand(0, 3),
                        ],
                        'away_team' => [
                            'name' => 'Cameroon',
                            'code' => 'CMR',
                            'flag' => '🇨🇲',
                            'score' => rand(0, 3),
                        ],
                        'competition' => 'FIFA World Cup 2026',
                        'stage' => 'Group Stage - Group B',
                        'venue' => 'AT&T Stadium, Texas',
                        'kickoff_time' => now()->subMinutes(rand(30, 90))->toIso8601String(),
                    ],
                ],
                'last_updated' => now()->toIso8601String(),
                'is_mock_data' => true,
            ],
        ];
    }

    /**
     * Increment total request counter for cache hit rate calculation
     */
    private function incrementTotalRequests(string $endpoint): void
    {
        $key = 'worldcup_total_requests_' . now()->format('Y-m-d-H');
        Cache::increment($key);
        Cache::put($key . '_ttl', now()->addHours(25)->timestamp);
    }
}
