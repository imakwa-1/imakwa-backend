<?php

namespace App\Services\WorldCup;

use Illuminate\Support\Facades\Log;

class ResponseTransformer
{
    /**
     * Transform Football-Data.org API response to standardized schema
     * Handles fixtures, upcoming, and live scores endpoints
     */
    public function transform(array $apiResponse): array
    {
        $matches = [];

        foreach ($apiResponse['matches'] ?? [] as $match) {
            try {
                $matches[] = $this->transformMatch($match);
            } catch (\Exception $e) {
                // Log individual match transformation errors but continue
                Log::channel('worldcup')->warning('Failed to transform match', [
                    'match_id' => $match['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => true,
            'data' => [
                'matches' => $matches,
                'last_updated' => now()->toIso8601String(),
                'is_mock_data' => false,
            ],
        ];
    }

    /**
     * Transform single match from Football-Data.org format
     */
    private function transformMatch(array $match): array
    {
        return [
            'id' => (string) $match['id'],
            'status' => $this->transformStatus($match['status']),
            'minute' => $this->getMinute($match),
            'home_team' => [
                'name' => $match['homeTeam']['name'] ?? 'TBD',
                'code' => $match['homeTeam']['tla'] ?? 'TBD',
                'flag' => $this->getFlag($match['homeTeam']['tla'] ?? null),
                'score' => $this->getScore($match, 'home'),
            ],
            'away_team' => [
                'name' => $match['awayTeam']['name'] ?? 'TBD',
                'code' => $match['awayTeam']['tla'] ?? 'TBD',
                'flag' => $this->getFlag($match['awayTeam']['tla'] ?? null),
                'score' => $this->getScore($match, 'away'),
            ],
            'competition' => $match['competition']['name'] ?? 'FIFA World Cup 2026',
            'stage' => $this->transformStage($match['stage'] ?? null, $match['group'] ?? null),
            'venue' => $match['venue'] ?? 'Venue TBD',
            'kickoff_time' => $match['utcDate'] ?? null,
        ];
    }

    /**
     * Transform match status from Football-Data.org to our format
     */
    private function transformStatus(string $status): string
    {
        return match($status) {
            'TIMED', 'SCHEDULED' => 'scheduled',
            'IN_PLAY', 'PAUSED', 'LIVE' => 'live',
            'FINISHED', 'AWARDED' => 'finished',
            'POSTPONED', 'CANCELLED' => 'scheduled', // Treat as scheduled
            default => 'scheduled',
        };
    }

    /**
     * Get current match minute
     */
    private function getMinute(array $match): ?int
    {
        if ($this->transformStatus($match['status']) !== 'live') {
            return null;
        }

        return $match['minute'] ?? $match['injuryTime'] ?? null;
    }

    /**
     * Get team score
     */
    private function getScore(array $match, string $side): ?int
    {
        $status = $this->transformStatus($match['status']);
        
        if ($status === 'scheduled') {
            return null;
        }

        $scoreKey = $side === 'home' ? 'home' : 'away';
        
        // Try fullTime first, then halfTime for live matches
        return $match['score']['fullTime'][$scoreKey] 
            ?? $match['score']['halfTime'][$scoreKey] 
            ?? 0;
    }

    /**
     * Transform stage/group to readable format
     */
    private function transformStage(?string $stage, ?string $group): string
    {
        if (!$stage) {
            return 'TBD';
        }

        $formatted = match($stage) {
            'GROUP_STAGE' => 'Group Stage',
            'ROUND_OF_16' => 'Round of 16',
            'QUARTER_FINALS' => 'Quarter Finals',
            'SEMI_FINALS' => 'Semi Finals',
            'FINAL' => 'Final',
            'THIRD_PLACE' => 'Third Place',
            default => ucwords(str_replace('_', ' ', strtolower($stage))),
        };

        if ($group) {
            $formatted .= ' - ' . $group;
        }

        return $formatted;
    }

    /**
     * Get country flag emoji from team code
     */
    private function getFlag(?string $code): string
    {
        if (!$code || strlen($code) !== 3) {
            return '🏁';
        }

        // Map 3-letter codes to 2-letter ISO codes for emoji flags
        $map = [
            'NGA' => '🇳🇬', 'GHA' => '🇬🇭', 'SEN' => '🇸🇳', 'CMR' => '🇨🇲',
            'EGY' => '🇪🇬', 'MAR' => '🇲🇦', 'RSA' => '🇿🇦', 'TUN' => '🇹🇳',
            'ALG' => '🇩🇿', 'CIV' => '🇨🇮', 'MLI' => '🇲🇱', 'BFA' => '🇧🇫',
            'USA' => '🇺🇸', 'MEX' => '🇲🇽', 'CAN' => '🇨🇦', 'BRA' => '🇧🇷',
            'ARG' => '🇦🇷', 'URU' => '🇺🇾', 'COL' => '🇨🇴', 'CHI' => '🇨🇱',
            'ESP' => '🇪🇸', 'GER' => '🇩🇪', 'FRA' => '🇫🇷', 'ENG' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'POR' => '🇵🇹', 'ITA' => '🇮🇹', 'NED' => '🇳🇱', 'BEL' => '🇧🇪',
            'JPN' => '🇯🇵', 'KOR' => '🇰🇷', 'AUS' => '🇦🇺', 'IRN' => '🇮🇷',
            'KSA' => '🇸🇦', 'QAT' => '🇶🇦',
        ];

        return $map[$code] ?? '🏁';
    }
}
