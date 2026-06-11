<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tournament Mode Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the live scores data source and behavior.
    | Managed via Filament admin panel (WorldCupSettings page).
    |
    */

    // Default to mock data until admin switches to live mode
    'use_mock' => env('WORLDCUP_USE_MOCK', true),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Configuration
    |--------------------------------------------------------------------------
    |
    | Cache durations for each endpoint to balance freshness vs API usage.
    | live_scores TTL automatically drops to 30s when tournament mode is live.
    |
    */

    'cache_ttl' => [
        'live_scores' => 60,    // 60s pre-tournament, 30s during tournament
        'upcoming'    => 300,   // 5 minutes
        'fixtures'    => 3600,  // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    'api_provider' => 'football-data',
    
    'football_data' => [
        'api_key' => env('FOOTBALL_DATA_API_KEY'),
        'base_url' => 'https://api.football-data.org/v4',
        'competition_code' => 'WC', // FIFA World Cup
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting & Monitoring
    |--------------------------------------------------------------------------
    |
    | Free tier: 10 requests/minute = 600 requests/hour
    | Warning threshold alerts admins before hitting limits.
    |
    */

    'rate_limit' => [
        'requests_per_hour_warning' => 500,  // Alert threshold
        'requests_per_hour_max'     => 600,  // Hard cap
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    */

    'upcoming_limit' => 10, // Number of upcoming matches to return

];
