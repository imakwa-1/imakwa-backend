<?php
namespace App\Filament\Resources\Artworks;

use App\Filament\Resources\Artworks\Pages;
use App\Models\Artwork;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ArtworkResource extends Resource
{
    protected static ?string $model = Artwork::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';
    protected static string|\UnitEnum|null $navigationGroup = 'Gallery';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $form): Schema
    {
        return $form->components([
            Select::make('artist_id')->relationship('artist', 'display_name')->required(),
            TextInput::make('title')->required(),
            Textarea::make('description')->columnSpanFull(),
            FileUpload::make('images')
                ->label('Artwork Images')
                ->image()
                ->multiple()
                ->disk('public')
                ->directory('artworks')
                ->maxFiles(10)
                ->reorderable()
                ->helperText('Upload multiple images. Drag to reorder.')
                ->columnSpanFull(),
            TextInput::make('medium'),
            TextInput::make('dimensions'),
            TextInput::make('year')->numeric(),
            TextInput::make('price')->numeric()->required(),
            TextInput::make('currency')->default('USD'),
            TextInput::make('category'),
            TextInput::make('region'),
            Select::make('status')->options([
                'available' => 'Available',
                'sold'      => 'Sold',
                'reserved'  => 'Reserved',
            ])->default('available'),
            Select::make('site_context')->options([
                'gallery'  => 'Gallery',
                'worldcup' => 'World Cup',
                'both'     => 'Both',
            ])->default('gallery'),
            Toggle::make('is_active')->default(true),
            Toggle::make('is_approved')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('artist.display_name')->label('Artist')->sortable(),
            TextColumn::make('price')->money('USD')->sortable(),
            TextColumn::make('status')->badge()->color(fn($state) => match($state) {
                'available' => 'success',
                'sold'      => 'danger',
                'reserved'  => 'warning',
                default     => 'gray',
            }),
            TextColumn::make('site_context')->badge(),
            IconColumn::make('is_approved')->boolean(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->filters([
            SelectFilter::make('status')->options([
                'available' => 'Available',
                'sold'      => 'Sold',
                'reserved'  => 'Reserved',
            ]),
            SelectFilter::make('site_context')->options([
                'gallery'  => 'Gallery',
                'worldcup' => 'World Cup',
                'both'     => 'Both',
            ]),
            TernaryFilter::make('is_approved'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArtworks::route('/'),
            'create' => Pages\CreateArtwork::route('/create'),
            'edit'   => Pages\EditArtwork::route('/{record}/edit'),
            'view'   => Pages\ViewArtwork::route('/{record}'),
        ];
    }
}