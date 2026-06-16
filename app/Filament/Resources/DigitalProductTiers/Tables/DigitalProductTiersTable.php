<?php

namespace App\Filament\Resources\DigitalProductTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DigitalProductTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('digital_product_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tier')
                    ->badge(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                
                // Legacy License System
                TextColumn::make('license_count')
                    ->label('Licenses (Legacy)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('licenses_sold')
                    ->label('Sold (Legacy)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // New Inventory System
                TextColumn::make('stock_available')
                    ->label('Stock Available')
                    ->badge()
                    ->color(fn ($record) => {
                        if ($record->is_unlimited) return 'success';
                        $available = $record->stock_available ?? 0;
                        if ($available > 10) return 'success';
                        if ($available > 0) return 'warning';
                        return 'danger';
                    })
                    ->formatStateUsing(fn ($record) => {
                        if ($record->is_unlimited) return 'Unlimited';
                        return ($record->stock_available ?? 0) . ' / ' . ($record->stock_quantity ?? 'N/A');
                    })
                    ->sortable(),
                
                TextColumn::make('download_url')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
