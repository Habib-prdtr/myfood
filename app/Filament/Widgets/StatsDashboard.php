<?php

namespace App\Filament\Widgets;

use App\Models\Foods;
use App\Models\Barcode;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $total = Transaction::sum('total');
        return [
            Stat::make('Total QR Code', Barcode::count() . ' Code'),
            Stat::make('Total Kategori Makanan', Category::count() . ' Kategori'),
            Stat::make('Total Menu Makanan', Foods::count() . ' Menu'),
            Stat::make('Total Transaksi', Transaction::count() . ' Transaksi'),
            Stat::make('Total Penghasilan', 'Rp ' . number_format($total, 0, ',', '.')),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}
