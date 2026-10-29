<?php

namespace App\Filament\Admin\Resources\SavingsGoalResource\Pages;

use App\Filament\Admin\Resources\SavingsGoalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSavingsGoal extends CreateRecord
{
    protected static string $resource = SavingsGoalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
