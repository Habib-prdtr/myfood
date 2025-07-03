@php
    use App\Models\Transaction;
    use App\Models\Foods;
    use App\Models\Barcode;
    use App\Models\Category;

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

    $chartLabels = $chartData->pluck('date')->map(fn ($d) => date('d M', strtotime($d)));
    $chartValues = $chartData->pluck('total_revenue');
@endphp

<x-filament::page>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-pulse-slow {
            animation: pulse 2s infinite;
        }
        
        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .chart-container {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .gradient-bg-1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-bg-2 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .gradient-bg-3 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .gradient-bg-4 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .gradient-bg-5 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .icon-wrapper {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        
        /* Custom spacing utilities */
        .section-spacing {
            margin-bottom: 1rem; /* 40px */
        }
        
        .card-spacing {
            margin-bottom: 1rem; /* 24px */
        }
        
        @media (min-width: 768px) {
            .section-spacing {
                margin-bottom: 1rem; /* 48px */
            }
        }
    </style>

    <div class="w-full">
        <!-- Stats Section - Top 3 Cards -->
        <section class="section-spacing animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach(array_slice($stats, 0, 3) as $index => $stat)
                    <div class="stat-card gradient-bg-{{ $index + 1 }} p-6 shadow-lg text-white relative overflow-hidden card-spacing rounded-lg">
                        <!-- Icon -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center">
                                @if($index == 0)
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code-icon lucide-qr-code"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>

                                @elseif($index == 1)
                                    <svg class="w-6 h-6"  xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tags-icon lucide-tags"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/>
                                        <path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a1 1 0 0 0-1 1v5.172a2 2 0 0 0 .586 1.414L8.29 18.29a2.426 2.426 0 0 0 3.42 0l3.58-3.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="6.5" cy="9.5" r=".5" fill="currentColor"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scroll-text-icon lucide-scroll-text"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>
                                @endif
                            </div>
                            <div class="animate-pulse-slow">
                                <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="space-y-3">
                            <p class="text-white/80 text-sm font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                            <p class="text-3xl font-bold text-white leading-tight">{{ $stat['value'] }}</p>
                        </div>
                        
                        <!-- Decorative Element -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Stats Section - Bottom 2 Cards -->
        @if(count($stats) > 3)
        <section class="section-spacing animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach(array_slice($stats, 3, 2) as $index => $stat)
                    <div class="stat-card gradient-bg-{{ $index + 4 }} p-6 shadow-lg text-white relative overflow-hidden card-spacing rounded-lg">
                        <!-- Icon -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="icon-wrapper w-12 h-12 rounded-xl flex items-center justify-center">
                                @if($index == 0)
                                    <svg class="w-6 h-6"xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote-arrow-up-icon lucide-banknote-arrow-up"><path d="M12 18H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5"/><path d="M18 12h.01"/><path d="M19 22v-6"/><path d="m22 19-3-3-3 3"/><path d="M6 12h.01"/><circle cx="12" cy="12" r="2"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-dollar-sign-icon lucide-circle-dollar-sign"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
                                    </svg>
                                @endif
                            </div>
                            <div class="animate-pulse-slow">
                                <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="space-y-3">
                            <p class="text-white/80 text-sm font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                            <p class="text-3xl font-bold text-white leading-tight">{{ $stat['value'] }}</p>
                        </div>
                        
                        <!-- Decorative Element -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full"></div>
                        <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-white/5 rounded-full"></div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Chart Section -->
        <section class="animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="chart-container p-10 shadow-xl mb-8 rounded-lg">
                <!-- Header dengan margin yang lebih besar dari border -->
                <div class="flex items-center justify-between mb-10 px-4 pt-2">
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-4">Pendapatan Per Hari</h3>
                        <p class="text-gray-400">Analisis performa harian Myfood</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse-slow"></div>
                        <span class="text-gray-400 text-sm">Live Data</span>
                    </div>
                </div>
                
                <!-- Chart wrapper dengan padding tambahan -->
                <div class="relative bg-slate-800/30 rounded-2xl p-6 mx-4">
                    <canvas id="revenueChart" class="w-full h-96"></canvas>
                </div>
                
                <!-- Bottom padding untuk chart container -->
                <div class="pb-2"></div>
            </div>
        </section>

    <!-- Bottom spacer -->
    <div class="h-8"></div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.1)');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Pendapatan (IDR)',
                        data: @json($chartValues),
                        backgroundColor: gradient,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        borderRadius: 12, // Lebih rounded
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20,
                            right: 20,
                            bottom: 20,
                            left: 20
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            cornerRadius: 12,
                            displayColors: false,
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                font: {
                                    size: 13,
                                    weight: '500'
                                },
                                padding: 10
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.08)',
                                drawBorder: false,
                                lineWidth: 1
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                font: {
                                    size: 13,
                                    weight: '500'
                                },
                                padding: 15,
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                        notation: 'compact',
                                        compactDisplay: 'short'
                                    }).format(value);
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        bar: {
                            borderRadius: 8,
                            borderSkipped: false,
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        });
    </script>
</x-filament::page>