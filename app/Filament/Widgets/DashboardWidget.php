<?php

namespace App\Filament\Widgets;

use App\Models\Foods;
use App\Models\Barcode;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-widget';
    protected static ?string $maxWidth = null;

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('admin');
    }

    protected function getViewData(): array
    {
        $total = Transaction::sum('total');

        $stats = [
            ['label' => 'Total QR Code', 'value' => Barcode::count()],
            ['label' => 'Total Kategori Makanan', 'value' => Category::count()],
            ['label' => 'Total Menu Makanan', 'value' => Foods::count()],
            ['label' => 'Total Transaksi', 'value' => Transaction::count()],
            ['label' => 'Total Penghasilan', 'value' => 'Rp ' . number_format($total, 0, ',', '.')],
        ];

        $chartData = Transaction::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'stats' => $stats,
            'chartLabels' => $chartData->pluck('date')->map(fn ($d) => date('d M', strtotime($d))),
            'chartValues' => $chartData->pluck('total_revenue'),
        ];
    }
    
}
