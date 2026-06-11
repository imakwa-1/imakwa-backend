<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 space-y-3">
            <x-filament::section>
                <x-slot name="heading">
                    Quick Actions
                </x-slot>

                <x-slot name="description">
                    One-click mode switching and cache management
                </x-slot>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Switch to Live Mode --}}
                    <x-filament::button
                        wire:click="switchToLiveMode"
                        color="success"
                        icon="heroicon-o-play"
                        size="lg"
                    >
                        Switch to Live Mode
                    </x-filament::button>

                    {{-- Switch to Pre-Tournament --}}
                    <x-filament::button
                        wire:click="switchToPreTournamentMode"
                        color="gray"
                        icon="heroicon-o-pause"
                        size="lg"
                    >
                        Switch to Pre-Tournament
                    </x-filament::button>
                </div>

                <div class="mt-6 border-t pt-6">
                    <h3 class="text-sm font-medium mb-3">Cache Management</h3>
                    
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <x-filament::button
                            wire:click="clearFixturesCache"
                            color="gray"
                            outlined
                            size="sm"
                        >
                            Clear Fixtures
                        </x-filament::button>

                        <x-filament::button
                            wire:click="clearLiveScoresCache"
                            color="gray"
                            outlined
                            size="sm"
                        >
                            Clear Live Scores
                        </x-filament::button>

                        <x-filament::button
                            wire:click="clearUpcomingCache"
                            color="gray"
                            outlined
                            size="sm"
                        >
                            Clear Upcoming
                        </x-filament::button>

                        <x-filament::button
                            wire:click="clearAllCache"
                            color="danger"
                            outlined
                            size="sm"
                        >
                            Clear All Caches
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>

            {{-- API Health Status --}}
            <x-filament::section>
                <x-slot name="heading">
                    API Health & Monitoring
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    <ul>
                        <li>
                            <strong>Free Tier Limit:</strong> 10 requests/minute (600/hour)
                        </li>
                        <li>
                            <strong>Current Strategy:</strong> 
                            Fixtures (1h cache), Upcoming (5min cache), Live Scores (60s/30s cache)
                        </li>
                        <li>
                            <strong>Estimated Usage:</strong> ~73 requests/hour (well under limit)
                        </li>
                        <li>
                            <strong>Logs:</strong> Check <code>storage/logs/worldcup.log</code> for API errors
                        </li>
                    </ul>

                    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-200 mb-0">
                            <strong>💡 Tip:</strong> The system automatically falls back to cached data if the API fails. 
                            Monitor the "Quota Status" above to ensure you're not approaching rate limits.
                        </p>
                    </div>
                </div>
            </x-filament::section>

            {{-- Phase 2 Instructions --}}
            <x-filament::section>
                <x-slot name="heading">
                    Phase 2: When World Cup Starts
                </x-slot>

                <div class="prose dark:prose-invert max-w-none">
                    <ol>
                        <li>Click <strong>"Switch to Live Mode"</strong> button above</li>
                        <li>System automatically:
                            <ul>
                                <li>Switches <code>/live-scores</code> to real API</li>
                                <li>Reduces cache TTL to 30 seconds</li>
                                <li>Enables rate limit monitoring</li>
                            </ul>
                        </li>
                        <li>Frontend sees <code>using_mock: false</code> in <code>/scores-status</code></li>
                        <li>Live scores widget appears automatically to customers</li>
                    </ol>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>No code deployment needed.</strong> Just flip the switch when the tournament begins.
                    </p>
                </div>
            </x-filament::section>
        </div>
    </form>
</x-filament-panels::page>
