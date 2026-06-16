<?php
namespace App\Filament\Resources\DigitalProductTiers;

use App\Filament\Resources\DigitalProductTiers\Pages;
use App\Models\DigitalProductTier;
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
use Filament\Actions\CreateAction;

class DigitalProductTierResource extends Resource
{
    protected static ?string $model = DigitalProductTier::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'WC Tiers';
    protected static string|\UnitEnum|null $navigationGroup = 'World Cup';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form->components([
            Select::make('digital_product_id')->relationship('product', 'name')->required()->label('Product'),
            Select::make('tier')->options([
                'I'   => 'Tier I',
                'II'  => 'Tier II',
                'III' => 'Tier III',
                'IV'  => 'Tier IV',
            ])->required(),
            TextInput::make('label')->required(),
            Textarea::make('description')->columnSpanFull(),
            TextInput::make('file_path')
                ->label('Digital File URL')
                ->placeholder('Upload file to cloud storage and paste URL here')
                ->helperText('Upload PDF/ZIP to Google Drive, Dropbox, or similar')
                ->columnSpanFull(),
            TextInput::make('price')->numeric()->required(),
            TextInput::make('currency')->default('USD'),
            
            // Legacy License System
            TextInput::make('license_count')
                ->numeric()
                ->required()
                ->label('Total Licenses (Legacy)')
                ->helperText('Legacy field - use Stock Quantity below instead'),
            TextInput::make('licenses_sold')
                ->numeric()
                ->default(0)
                ->label('Licenses Sold (Legacy)')
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
            
            TextInput::make('download_url')->label('Download URL'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('product.name')
                ->label('Product')
                ->sortable()
                ->searchable(),
            
            TextColumn::make('tier')
                ->badge()
                ->sortable()
                ->color('warning'),
            
            TextColumn::make('label')
                ->searchable()
                ->weight('bold'),
            
            TextColumn::make('price')
                ->money('USD')
                ->sortable(),
            
            // New Stock System
            TextColumn::make('stock_available')
                ->label('Stock')
                ->badge()
                ->color(fn($record) => 
                    $record->is_unlimited ? 'success' : (
                        ($record->stock_available ?? 0) > 10 ? 'success' : (
                            ($record->stock_available ?? 0) > 0 ? 'warning' : 'danger'
                        )
                    )
                )
                ->formatStateUsing(fn($record) => 
                    $record->is_unlimited 
                        ? 'Unlimited' 
                        : (($record->stock_available ?? 0) . ' / ' . ($record->stock_quantity ?? 'N/A'))
                ),
            
            // Legacy columns (hidden by default)
            TextColumn::make('licenses_sold')
                ->label('Sold (Legacy)')
                ->badge()
                ->color('success')
                ->toggleable(isToggledHiddenByDefault: true),
            
            TextColumn::make('license_count')
                ->label('Total (Legacy)')
                ->toggleable(isToggledHiddenByDefault: true),
            
            TextColumn::make('availability')
                ->label('Available (Legacy)')
                ->getStateUsing(fn ($record) => $record->license_count - $record->licenses_sold)
                ->badge()
                ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                ->toggleable(isToggledHiddenByDefault: true),
            
            IconColumn::make('is_active')
                ->boolean()
                ->label('Active'),
        ])
        ->filters([
            SelectFilter::make('tier')->options([
                'I' => 'Tier I', 'II' => 'Tier II', 'III' => 'Tier III', 'IV' => 'Tier IV',
            ]),
            TernaryFilter::make('is_active'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDigitalProductTiers::route('/'),
            'create' => Pages\CreateDigitalProductTier::route('/create'),
            'edit'   => Pages\EditDigitalProductTier::route('/{record}/edit'),
            'view'   => Pages\ViewDigitalProductTier::route('/{record}'),
        ];
    }
}