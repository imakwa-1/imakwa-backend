<?php
namespace App\Filament\Resources\DigitalProducts;

use App\Filament\Resources\DigitalProducts\Pages;
use App\Models\DigitalProduct;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;

class DigitalProductResource extends Resource
{
    protected static ?string $model = DigitalProduct::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'WC Products';
    protected static string|\UnitEnum|null $navigationGroup = 'World Cup';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->label('Product Name')
                ->placeholder('e.g., Nigeria World Cup Collection'),
            
            TextInput::make('country')
                ->required()
                ->maxLength(255)
                ->label('Country'),
            
            TextInput::make('flag_emoji')
                ->maxLength(10)
                ->label('Flag Emoji')
                ->placeholder('🇳🇬')
                ->helperText('Optional flag emoji for display'),
            
            Textarea::make('description')
                ->rows(3)
                ->maxLength(1000)
                ->label('Description'),
            
            TextInput::make('cover_image')
                ->url()
                ->maxLength(255)
                ->label('Cover Image URL')
                ->placeholder('https://...'),
            
            DateTimePicker::make('closes_at')
                ->label('Store Closes At')
                ->helperText('Set when this product becomes unavailable')
                ->native(false),
            
            Toggle::make('is_active')
                ->label('Active')
                ->helperText('Make this product available for purchase')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('flag_emoji')
                ->label('')
                ->size('lg'),
            
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->weight('bold'),
            
            TextColumn::make('country')
                ->searchable()
                ->sortable(),
            
            TextColumn::make('closes_at')
                ->dateTime('M j, Y g:i A')
                ->sortable()
                ->label('Closes')
                ->color(fn ($record) => $record->closes_at && $record->closes_at->isPast() ? 'danger' : 'success'),
            
            IconColumn::make('is_active')
                ->boolean()
                ->label('Active'),
            
            TextColumn::make('tiers_count')
                ->counts('tiers')
                ->label('Tiers')
                ->badge(),
            
            TextColumn::make('created_at')
                ->dateTime('M j, Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            TernaryFilter::make('is_active')->label('Active Products'),
        ])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDigitalProducts::route('/'),
            'create' => Pages\CreateDigitalProduct::route('/create'),
            'edit'   => Pages\EditDigitalProduct::route('/{record}/edit'),
            'view'   => Pages\ViewDigitalProduct::route('/{record}'),
        ];
    }
}