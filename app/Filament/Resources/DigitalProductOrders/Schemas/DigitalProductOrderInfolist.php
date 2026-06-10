<?php

namespace App\Filament\Resources\DigitalProductOrders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DigitalProductOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reference'),
                TextEntry::make('digital_product_tier_id')
                    ->numeric(),
                TextEntry::make('email')
                    ->label('Email address'),
                IconEntry::make('token_used')
                    ->boolean(),
                TextEntry::make('token_expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('payment_status')
                    ->badge(),
                TextEntry::make('payment_reference')
                    ->placeholder('-'),
                TextEntry::make('payment_intent_id')
                    ->placeholder('-'),
                TextEntry::make('paystack_reference')
                    ->placeholder('-'),
                TextEntry::make('payment_gateway')
                    ->placeholder('-'),
                TextEntry::make('amount_paid')
                    ->numeric()
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
