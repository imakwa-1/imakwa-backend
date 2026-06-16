<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Mark as Processing
            Action::make('mark_processing')
                ->label('Mark as Processing')
                ->icon('heroicon-o-cog')
                ->color('info')
                ->visible(fn() => $this->record->status === 'pending')
                ->form([
                    Textarea::make('notes')
                        ->label('Notes (Optional)')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    try {
                        $this->record->transitionTo('processing', $data['notes'] ?? null);
                        Notification::make()
                            ->title('Order marked as processing')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Ship Order
            Action::make('ship_order')
                ->label('Ship Order')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->visible(fn() => in_array($this->record->status, ['pending', 'processing']))
                ->form([
                    TextInput::make('tracking_number')
                        ->label('Tracking Number')
                        ->required(),
                    Textarea::make('notes')
                        ->label('Notes (Optional)')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    try {
                        $this->record->transitionTo(
                            'shipped',
                            $data['notes'] ?? null,
                            $data['tracking_number']
                        );
                        Notification::make()
                            ->title('Order shipped successfully')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status', 'tracking_number']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Mark as Delivered
            Action::make('mark_delivered')
                ->label('Mark as Delivered')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === 'shipped')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        $this->record->transitionTo('delivered');
                        Notification::make()
                            ->title('Order marked as delivered')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Cancel Order
            Action::make('cancel_order')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => !in_array($this->record->status, ['delivered', 'cancelled']))
                ->form([
                    Textarea::make('cancellation_reason')
                        ->label('Cancellation Reason')
                        ->required()
                        ->rows(3),
                    Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    try {
                        $this->record->transitionTo(
                            'cancelled',
                            $data['notes'] ?? null,
                            null,
                            $data['cancellation_reason']
                        );
                        Notification::make()
                            ->title('Order cancelled successfully')
                            ->body('Stock has been restored.')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
