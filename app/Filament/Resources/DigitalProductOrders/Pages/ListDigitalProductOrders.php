<?php

namespace App\Filament\Resources\DigitalProductOrders\Pages;

use App\Filament\Resources\DigitalProductOrders\DigitalProductOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalProductOrders extends ListRecords
{
    protected static string $resource = DigitalProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
