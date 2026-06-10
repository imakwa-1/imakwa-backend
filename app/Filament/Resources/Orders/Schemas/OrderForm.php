<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('reference')
                    ->required(),
                Select::make('fulfillment_type')
                    ->options(['physical' => 'Physical', 'digital' => 'Digital'])
                    ->default('physical')
                    ->required(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_gateway')
                    ->options(['stripe' => 'Stripe', 'paystack' => 'Paystack'])
                    ->default(null),
                TextInput::make('payment_reference')
                    ->default(null),
                TextInput::make('payment_intent_id')
                    ->default(null),
                TextInput::make('paystack_reference')
                    ->default(null),
                Select::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('shipping_name')
                    ->default(null),
                TextInput::make('shipping_email')
                    ->email()
                    ->default(null),
                TextInput::make('shipping_phone')
                    ->tel()
                    ->default(null),
                TextInput::make('shipping_address')
                    ->default(null),
                TextInput::make('shipping_city')
                    ->default(null),
                TextInput::make('shipping_country')
                    ->default(null),
                TextInput::make('shipping_postal_code')
                    ->default(null),
            ]);
    }
}
