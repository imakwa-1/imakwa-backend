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
                Select::make('status')
                    ->options(['available' => 'Available', 'sold' => 'Sold', 'reserved' => 'Reserved'])
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
