<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use App\Services\WorldCup\FootballDataService;

class WorldCupSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'World Cup Settings';

    protected static ?int $navigationSort = 100;

    // Note: $view is inherited from parent Page class as non-static property

    public function getView(): string
    {
        return 'filament.pages.world-cup-settings';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'use_mock' => (bool) Cache::get('worldcup_use_mock', true),
            'tournament_mode' => Cache::get('worldcup_tournament_mode', 'pre-tournament'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tournament Status')
                    ->description('Control whether the live scores system uses mock data or real API data')
                    ->schema([
                        Toggle::make('use_mock')
                            ->label('Use Mock Data')
                            ->helperText('When enabled, /live-scores returns mock data. Disable to use real Football-Data.org API.')
                            ->inline(false)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                Cache::put('worldcup_use_mock', $state, 86400);
                                Cache::forget('worldcup_live_scores');
                                
                                Notification::make()
                                    ->title($state ? 'Switched to Mock Data' : 'Switched to Real API')
                                    ->success()
                                    ->send();
                            }),

                        Placeholder::make('tournament_mode_info')
                            ->label('Current Mode')
                            ->content(function () {
                                $useMock = (bool) Cache::get('worldcup_use_mock', true);
                                $mode = Cache::get('worldcup_tournament_mode', 'pre-tournament');
                                
                                if ($useMock) {
                                    return '⚪ Pre-Tournament (Mock Data)';
                                }
                                
                                return $mode === 'live' ? '⚫ Live Tournament (Real API)' : '⚪ Pre-Tournament (Real Fixtures)';
                            }),

                        Placeholder::make('mode_description')
                            ->label('What This Means')
                            ->content(function () {
                                $useMock = (bool) Cache::get('worldcup_use_mock', true);
                                
                                if ($useMock) {
                                    return 'Live scores endpoint returns mock data. Frontend should hide live scores widget from customers. Use for development/staging only.';
                                }
                                
                                return 'Live scores endpoint uses Football-Data.org API. Cache TTL automatically reduced to 30 seconds. Rate limit monitoring active.';
                            }),
                    ]),

                Section::make('Cache Management')
                    ->description('View cache statistics and clear cached data')
                    ->schema([
                        Placeholder::make('last_api_call')
                            ->label('Last API Call')
                            ->content(fn () => Cache::get('worldcup_last_api_call', 'Never')),

                        Placeholder::make('last_updated')
                            ->label('Last Updated')
                            ->content(fn () => Cache::get('worldcup_last_updated', 'Never')),

                        Placeholder::make('requests_this_hour')
                            ->label('API Requests This Hour')
                            ->content(function () {
                                $count = app(FootballDataService::class)->getRequestCount();
                                $max = config('worldcup.rate_limit.requests_per_hour_max');
                                return "{$count} / {$max}";
                            }),

                        Placeholder::make('cache_hit_rate')
                            ->label('Cache Hit Rate')
                            ->content(function () {
                                $rate = app(FootballDataService::class)->getCacheHitRate();
                                return number_format($rate * 100, 1) . '%';
                            }),

                        Placeholder::make('quota_status')
                            ->label('Quota Status')
                            ->content(function () {
                                $warning = Cache::get('worldcup_quota_warning', false);
                                return $warning ? '⚠️ Approaching Limit' : '✅ OK';
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function clearFixturesCache(): void
    {
        Cache::forget('worldcup_fixtures');
        Cache::forget('worldcup_fixtures_backup');

        Notification::make()
            ->title('Fixtures cache cleared')
            ->success()
            ->send();
    }

    public function clearLiveScoresCache(): void
    {
        Cache::forget('worldcup_live_scores');
        Cache::forget('worldcup_live_scores_backup');

        Notification::make()
            ->title('Live scores cache cleared')
            ->success()
            ->send();
    }

    public function clearUpcomingCache(): void
    {
        Cache::forget('worldcup_upcoming');
        Cache::forget('worldcup_upcoming_backup');

        Notification::make()
            ->title('Upcoming matches cache cleared')
            ->success()
            ->send();
    }

    public function clearAllCache(): void
    {
        Cache::forget('worldcup_fixtures');
        Cache::forget('worldcup_fixtures_backup');
        Cache::forget('worldcup_live_scores');
        Cache::forget('worldcup_live_scores_backup');
        Cache::forget('worldcup_upcoming');
        Cache::forget('worldcup_upcoming_backup');
        Cache::forget('worldcup_last_api_call');
        Cache::forget('worldcup_last_updated');

        Notification::make()
            ->title('All World Cup caches cleared')
            ->success()
            ->send();
    }

    public function switchToLiveMode(): void
    {
        Cache::put('worldcup_use_mock', false, 86400);
        Cache::put('worldcup_tournament_mode', 'live', 86400);
        Cache::forget('worldcup_live_scores');

        $this->form->fill([
            'use_mock' => false,
            'tournament_mode' => 'live',
        ]);

        Notification::make()
            ->title('Switched to Live Tournament Mode')
            ->body('Live scores now using real API. Cache TTL reduced to 30 seconds.')
            ->success()
            ->send();
    }

    public function switchToPreTournamentMode(): void
    {
        Cache::put('worldcup_use_mock', true, 86400);
        Cache::put('worldcup_tournament_mode', 'pre-tournament', 86400);
        Cache::forget('worldcup_live_scores');

        $this->form->fill([
            'use_mock' => true,
            'tournament_mode' => 'pre-tournament',
        ]);

        Notification::make()
            ->title('Switched to Pre-Tournament Mode')
            ->body('Live scores now using mock data. Cache TTL restored to 60 seconds.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('clearAllCache')
                ->label('Clear All Cache')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action('clearAllCache'),
        ];
    }
}
