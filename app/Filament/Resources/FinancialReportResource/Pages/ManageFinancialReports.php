<?php

namespace App\Filament\Resources\FinancialReportResource\Pages;

use App\Filament\Resources\FinancialReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFinancialReports extends ManageRecords
{
    protected static string $resource = FinancialReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
