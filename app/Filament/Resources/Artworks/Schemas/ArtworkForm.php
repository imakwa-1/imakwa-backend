<?php

namespace App\Filament\Resources\Artworks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ArtworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('artist_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Artwork Images')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->reorderable()
                    ->helperText('Upload multiple images. The first image will be set as primary.')
                    ->columnSpanFull(),
                TextInput::make('medium')
                    ->default(null),
                TextInput::make('dimensions')
                    ->default(null),
                TextInput::make('year')
                    ->numeric()
                    ->default(null),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                
                // Inventory Management Section
                TextInput::make('stock_quantity')
                    ->label('Stock Quantity')
                    ->helperText('Total units available for sale (set to 1 for unique pieces)')
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->required(),
                TextInput::make('stock_sold')
                    ->label('Units Sold')
                    ->helperText('Automatically tracked. Edit only for corrections.')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                
                Select::make('status')
                    ->options([
                        'available' => 'Available', 
                        'sold' => 'Sold', 
                        'reserved' => 'Reserved',
                        'out_of_stock' => 'Out of Stock'
                    ])
                    ->default('available')
                    ->required(),
                Select::make('site_context')
                    ->options(['gallery' => 'Gallery', 'worldcup' => 'Worldcup', 'both' => 'Both'])
                    ->default('gallery')
                    ->required(),
                TextInput::make('category')
                    ->default(null),
                TextInput::make('region')
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_approved')
                    ->required(),
            ]);
    }
}
