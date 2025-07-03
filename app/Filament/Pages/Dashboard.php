<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole('admin');
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('admin');
    }
}
