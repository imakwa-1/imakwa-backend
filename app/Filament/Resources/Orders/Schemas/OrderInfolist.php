<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('reference'),
                TextEntry::make('fulfillment_type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_gateway')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('payment_reference')
                    ->placeholder('-'),
                TextEntry::make('payment_intent_id')
                    ->placeholder('-'),
                TextEntry::make('paystack_reference')
                    ->placeholder('-'),
                TextEntry::make('payment_status')
                    ->badge(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('shipping_cost')
                    ->money(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('shipping_name')
                    ->placeholder('-'),
                TextEntry::make('shipping_email')
                    ->placeholder('-'),
                TextEntry::make('shipping_phone')
                    ->placeholder('-'),
                TextEntry::make('shipping_address')
                    ->placeholder('-'),
                TextEntry::make('shipping_city')
                    ->placeholder('-'),
                TextEntry::make('shipping_country')
                    ->placeholder('-'),
                TextEntry::make('shipping_postal_code')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
