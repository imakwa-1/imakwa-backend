<?php

namespace App\Filament\Resources\DigitalProductOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DigitalProductOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('tier.digital_product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tier.label')
                    ->label('Tier')
                    ->badge()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Customer Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('amount_paid')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ])
                    ->sortable(),
                TextColumn::make('payment_gateway')
                    ->badge()
                    ->toggleable(),
                IconColumn::make('token_used')
                    ->label('Downloaded')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('token_expires_at')
                    ->label('Link Expires')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Purchase Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
