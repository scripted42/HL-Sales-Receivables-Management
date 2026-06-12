<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_tour')
                ->label('Panduan Pengguna')
                ->icon('heroicon-o-question-mark-circle')
                ->color('indigo')
                ->extraAttributes([
                    'id' => 'start-tour-btn',
                ]),
        ];
    }
}
