<?php

namespace App\Filament\Resources\DigitalProductOrders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DigitalProductOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->required(),
                TextInput::make('digital_product_tier_id')
                    ->required()
                    ->numeric(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Toggle::make('token_used')
                    ->required(),
                DateTimePicker::make('token_expires_at'),
                Select::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
                TextInput::make('payment_reference')
                    ->default(null),
                TextInput::make('payment_intent_id')
                    ->default(null),
                TextInput::make('paystack_reference')
                    ->default(null),
                TextInput::make('payment_gateway')
                    ->default(null),
                TextInput::make('amount_paid')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
