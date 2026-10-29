<?php

namespace App\Filament\Admin\Resources\SavingsGoalResource\Pages;

use App\Filament\Admin\Resources\SavingsGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavingsGoal extends EditRecord
{
    protected static string $resource = SavingsGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
