<?php

namespace App\Filament\Resources\Artworks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArtworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('artist_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('medium')
                    ->searchable(),
                TextColumn::make('dimensions')
                    ->searchable(),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                
                // Stock Information
                TextColumn::make('stock_available')
                    ->label('In Stock')
                    ->badge()
                    ->color(fn ($record) => {
                        $available = $record->stock_available ?? 0;
                        if ($available > 5) return 'success';
                        if ($available > 0) return 'warning';
                        return 'danger';
                    })
                    ->formatStateUsing(fn ($record) => 
                        ($record->stock_available ?? 0) . ' / ' . ($record->stock_quantity ?? 1)
                    )
                    ->sortable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'available',
                        'warning' => 'reserved',
                        'danger' => 'sold',
                        'secondary' => 'out_of_stock',
                    ]),
                TextColumn::make('site_context')
                    ->badge(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('region')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_approved')
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
