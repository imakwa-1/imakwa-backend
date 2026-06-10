<?php

namespace App\Filament\Resources\DigitalProductOrders\Pages;

use App\Filament\Resources\DigitalProductOrders\DigitalProductOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalProductOrder extends EditRecord
{
    protected static string $resource = DigitalProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
