<?php

namespace App\Filament\Resources\DigitalProductTiers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DigitalProductTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('digital_product_id')
                    ->required()
                    ->numeric(),
                Select::make('tier')
                    ->options(['I' => 'I', 'II' => 'I i', 'III' => 'I i i', 'IV' => 'I v'])
                    ->required(),
                TextInput::make('label')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('file_path')
                    ->label('Digital File')
                    ->helperText('Upload the digital product file (PDF, ZIP, etc.) that users will download after purchase.')
                    ->acceptedFileTypes(['application/pdf', 'application/zip', 'application/x-zip-compressed'])
                    ->maxSize(102400) // 100MB
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                
                // Legacy License System (kept for backward compatibility)
                TextInput::make('license_count')
                    ->label('License Count (Legacy)')
                    ->helperText('Legacy field - use Stock Quantity below instead')
                    ->numeric()
                    ->default(0),
                TextInput::make('licenses_sold')
                    ->label('Licenses Sold (Legacy)')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                
                // New Inventory System
                TextInput::make('stock_quantity')
                    ->label('Stock Quantity')
                    ->helperText('Leave empty for unlimited digital copies. Set a number for limited edition.')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->minValue(0),
                TextInput::make('stock_sold')
                    ->label('Units Sold')
                    ->helperText('Automatically tracked. Edit only for corrections.')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                
                TextInput::make('download_url')
                    ->url()
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
