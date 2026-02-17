<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    // 👇 ADD THIS - filter widgets by canView()
    public function getWidgets(): array
    {
        return collect(parent::getWidgets())
            ->filter(fn ($widget) => $widget::canView())
            ->toArray();
    }
}