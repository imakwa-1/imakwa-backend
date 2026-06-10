<?php

namespace App\Filament\Resources\DigitalProductOrders;

use App\Filament\Resources\DigitalProductOrders\Pages\CreateDigitalProductOrder;
use App\Filament\Resources\DigitalProductOrders\Pages\EditDigitalProductOrder;
use App\Filament\Resources\DigitalProductOrders\Pages\ListDigitalProductOrders;
use App\Filament\Resources\DigitalProductOrders\Pages\ViewDigitalProductOrder;
use App\Filament\Resources\DigitalProductOrders\Schemas\DigitalProductOrderForm;
use App\Filament\Resources\DigitalProductOrders\Schemas\DigitalProductOrderInfolist;
use App\Filament\Resources\DigitalProductOrders\Tables\DigitalProductOrdersTable;
use App\Models\DigitalProductOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DigitalProductOrderResource extends Resource
{
    protected static ?string $model = DigitalProductOrder::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static string|\UnitEnum|null $navigationGroup = 'World Cup';

    public static function form(Schema $schema): Schema
    {
        return DigitalProductOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DigitalProductOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalProductOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalProductOrders::route('/'),
            'create' => CreateDigitalProductOrder::route('/create'),
            'view' => ViewDigitalProductOrder::route('/{record}'),
            'edit' => EditDigitalProductOrder::route('/{record}/edit'),
        ];
    }
}
