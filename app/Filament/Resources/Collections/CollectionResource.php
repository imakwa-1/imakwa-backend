<?php
namespace App\Filament\Resources\Collections;

use App\Filament\Resources\Collections\Pages;
use App\Models\Collection;
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

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Gallery';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form->components([
            Select::make('artist_id')->relationship('artist', 'display_name')->required(),
            TextInput::make('name')->required(),
            Textarea::make('description')->columnSpanFull(),
            FileUpload::make('cover_image')
                ->image()
                ->disk('public')
                ->directory('collections')
                ->columnSpanFull(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('artist.display_name')->label('Artist')->sortable(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->filters([TernaryFilter::make('is_active')]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit'   => Pages\EditCollection::route('/{record}/edit'),
            'view'   => Pages\ViewCollection::route('/{record}'),
        ];
    }
}