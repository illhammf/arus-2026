<?php

namespace App\Filament\Admin\Resources\SavingsGoalResource\Pages;

use App\Filament\Admin\Resources\SavingsGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSavingsGoals extends ListRecords
{
    protected static string $resource = SavingsGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
