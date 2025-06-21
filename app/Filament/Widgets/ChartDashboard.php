<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ChartDashboard extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Per Hari';
    protected static ?int $sort = 2;

    public static function canView(): bool
{
    return Auth::user()?->hasRole('admin');
}

    protected function getData(): array
    {
        // Ambil data total transaksi per tanggal (7 hari terakhir misalnya)
        $data = Transaction::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total_revenue')
            ->where('created_at', '>=', now()->subDays(60))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn ($d) => date('d M', strtotime($d)))->toArray(),
            'datasets' => [
                [
                    'label' => 'Pendapatan (IDR)',
                    'data' => $data->pluck('total_revenue')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)',
                ],
            ],
        ];
    }
    

    protected function getType(): string
    {
        return 'bar';
    }
}
