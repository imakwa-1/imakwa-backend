<?php

namespace App\Filament\Resources\DigitalProductOrders\Pages;

use App\Filament\Resources\DigitalProductOrders\DigitalProductOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDigitalProductOrder extends ViewRecord
{
    protected static string $resource = DigitalProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
