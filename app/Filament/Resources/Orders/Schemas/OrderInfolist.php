<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Summary')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reference')
                                    ->label('Order Reference')
                                    ->weight('bold'),
                                TextEntry::make('total')
                                    ->label('Total Amount')
                                    ->money(fn($record) => $record->currency),
                                TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime('M d, Y h:i A'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn($state) => match($state) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('payment_status')
                                    ->label('Payment Status')
                                    ->badge()
                                    ->color(fn($state) => match($state) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('payment_gateway')
                                    ->label('Payment Gateway')
                                    ->badge()
                                    ->placeholder('-'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tracking_number')
                                    ->label('Tracking Number')
                                    ->placeholder('Not Shipped Yet'),
                                TextEntry::make('shipped_at')
                                    ->label('Shipped At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Not Shipped Yet'),
                                TextEntry::make('delivered_at')
                                    ->label('Delivered At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Not Delivered Yet'),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('cancellation_reason')
                                    ->label('Cancellation Reason')
                                    ->visible(fn($record) => $record->status === 'cancelled')
                                    ->placeholder('-'),
                                TextEntry::make('admin_notes')
                                    ->label('Admin Notes')
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Customer Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('customer_name')
                                    ->label('Name')
                                    ->getStateUsing(fn($record) => $record->customer_name),
                                TextEntry::make('customer_email')
                                    ->label('Email')
                                    ->getStateUsing(fn($record) => $record->customer_email),
                                TextEntry::make('shipping_phone')
                                    ->label('Phone')
                                    ->placeholder('-'),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('shipping_address')
                                    ->label('Shipping Address')
                                    ->getStateUsing(fn($record) => sprintf(
                                        '%s, %s, %s %s',
                                        $record->shipping_address ?? 'N/A',
                                        $record->shipping_city ?? 'N/A',
                                        $record->shipping_country ?? 'N/A',
                                        $record->shipping_postal_code ?? ''
                                    )),
                            ]),
                    ]),

                Section::make('Fulfillment Timeline')
                    ->schema([
                        ViewEntry::make('timeline')
                            ->label('')
                            ->view('filament.resources.orders.components.timeline'),
                    ]),
            ]);
    }
}
