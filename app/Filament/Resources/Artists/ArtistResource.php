<?php
namespace App\Filament\Resources\Artists;

use App\Filament\Resources\Artists\Pages;
use App\Models\Artist;
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
use Filament\Tables\Filters\TernaryFilter;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Gallery';
    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Schema $form): Schema
    {
        return $form->components([
            Select::make('user_id')->relationship('user', 'name')->required(),
            TextInput::make('display_name')->required(),
            TextInput::make('country')->required(),
            TextInput::make('region'),
            Textarea::make('bio')->columnSpanFull(),
            TextInput::make('profile_image')
                ->label('Profile Image URL')
                ->placeholder('Paste image URL here (e.g., from Imgur, Cloudinary)')
                ->helperText('Upload your image to a service like Imgur.com or use a direct image URL')
                ->columnSpanFull(),
            TextInput::make('instagram'),
            TextInput::make('website'),
            Toggle::make('is_verified'),
            Toggle::make('is_active')->default(true),
            Toggle::make('is_featured')
                ->label('Feature as Spotlight Artist')
                ->helperText('Only ONE artist should be featured at a time'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('display_name')->searchable(),
            TextColumn::make('country')->sortable(),
            TextColumn::make('user.name')->label('User'),
            IconColumn::make('is_verified')->boolean(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->filters([
            TernaryFilter::make('is_verified'),
            TernaryFilter::make('is_active'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit'   => Pages\EditArtist::route('/{record}/edit'),
            'view'   => Pages\ViewArtist::route('/{record}'),
        ];
    }
}