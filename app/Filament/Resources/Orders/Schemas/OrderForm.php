<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use App\Models\Order;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        Placeholder::make('reference')
                            ->content(fn(?Order $record) => $record?->reference ?? 'N/A'),
                        Placeholder::make('created_at')
                            ->label('Order Date')
                            ->content(fn(?Order $record) => $record?->created_at?->format('M d, Y h:i A') ?? 'N/A'),
                        Placeholder::make('customer_name')
                            ->label('Customer')
                            ->content(fn(?Order $record) => $record?->customer_name ?? 'N/A'),
                    ])
                    ->columns(3),

                Section::make('Status Management')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->disabled(),

                        TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->disabled(),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Payment Information')
                    ->schema([
                        Placeholder::make('payment_status')
                            ->content(fn(?Order $record) => ucfirst($record?->payment_status ?? 'N/A')),
                        Placeholder::make('total')
                            ->content(fn(?Order $record) => $record?->currency . ' ' . number_format($record?->total ?? 0, 2)),
                    ])
                    ->columns(2),
            ]);
    }
}
