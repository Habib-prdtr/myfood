<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Filament\Resources\TransactionResource;

class StaffPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
{
    return $panel
        ->id('staff')
        ->path('staff')
        ->brandName('Panel Staff')
        ->login()
        ->colors([
            'primary' => Color::Amber,
        ])
        ->authGuard('web') // ini penting
    
        ->resources([
            TransactionResource::class,
        ])
        ->middleware([
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
        ])
        ->authMiddleware([
            Authenticate::class,
        ]);
}

}