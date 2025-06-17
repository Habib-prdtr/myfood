<x-layouts.page title="Pesanan" class="min-h-screen bg-gradient-to-br from-orange-50 to-yellow-50 p-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl p-6 mb-6 shadow-lg">
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">🍽️ Daftar Pesanan Anda</h1>
            <p class="text-orange-100">Pantau status pesanan makanan Anda di sini</p>
        </div>

        @forelse ($orders as $order)
            <div class="mb-6 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-orange-100">
                <!-- Order Header -->
                <div class="bg-gradient-to-r from-orange-400 to-yellow-400 p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-3 mb-2 md:mb-0">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                                <span class="text-orange-500 font-bold text-lg">👤</span>
                            </div>
                            <div>
                                <p class="font-semibold text-white text-lg">{{ $order->name }}</p>
                                <p class="text-orange-100 text-sm">Order #{{ $order->id ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($order->status_pesanan == 'pending') bg-yellow-200 text-yellow-800
                                @elseif($order->status_pesanan == 'processing') bg-orange-200 text-orange-800
                                @elseif($order->status_pesanan == 'completed') bg-green-200 text-green-800
                                @elseif($order->status_pesanan == 'cancelled') bg-red-200 text-red-800
                                @else bg-gray-200 text-gray-800
                                @endif">
                                @if($order->status_pesanan == 'pending') ⏳ Menunggu
                                @elseif($order->status_pesanan == 'processing') 🍳 Diproses
                                @elseif($order->status_pesanan == 'completed') ✅ Selesai
                                @elseif($order->status_pesanan == 'cancelled') ❌ Dibatalkan
                                @else {{ $order->status_pesanan }}
                                @endif
                            </span>
                            
                            <!-- Total Price -->
                            <div class="bg-white bg-opacity-20 rounded-lg px-3 py-2">
                                <p class="text-white font-bold text-lg">Rp{{ number_format($order->total, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-sm">🍽️</span>
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg">Detail Makanan</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach ($order->Items as $item)
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg border border-orange-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-yellow-400 rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold">🍕</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $item->food->name ?? 'Makanan tidak ditemukan' }}</p>
                                        <p class="text-sm text-gray-600">Jumlah: {{ $item->quantity }}x</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-orange-600">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-400 to-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">🍽️</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Pesanan</h3>
                <p class="text-gray-500 mb-6">Anda belum memiliki pesanan apapun saat ini</p>
                <a href="{{ route('product.index') ?? '#' }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-yellow-500 text-white font-semibold rounded-lg hover:from-orange-600 hover:to-yellow-600 transition-all duration-300 shadow-md hover:shadow-lg">
                    <span class="mr-2">🛒</span>
                    Mulai Pesan Sekarang
                </a>
            </div>
        @endforelse
    </div>

<script>
    async function cekStatusPesanan() {
        console.log("Cek status pesanan...");

        try {
            const response = await fetch('/cek-status-pesanan');
            const data = await response.json();
            console.log("Response:", data);

            if (data && data.status === "diantar") {
                if (Notification.permission === "granted") {
                    new Notification("Pesanan Sudah Diantar 🍽️", {
                        body: `Pesanan atas nama ${data.nama} telah diantar!`
                    });
                }
                showToast(`Pesanan atas nama ${data.nama} telah diantar!`);
            } else if (data && data.status === "diproses") {
                showToastProses(`Pesanan atas nama ${data.nama} sedang diproses!`);
            }

        } catch (e) {
            console.error("Gagal fetch status pesanan:", e);
        }
    }


    function showToast(message) {
        playSound();
        const container = document.getElementById("toast-container");

        const toast = document.createElement("div");
        toast.className = `
            flex items-start gap-3 max-w-xs p-4 rounded-2xl bg-green-100 border border-green-300 shadow-lg
            animate-slide-in-fade
        `;

        toast.innerHTML = `
            <div class="flex-shrink-0 mt-1 text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-green-800">
                ${message}
            </div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("opacity-0", "translate-y-4", "transition-all", "duration-500");
            setTimeout(() => toast.remove(), 500);
        }, 6000);
    }

    function showToastProses(message) {
        playSound();
        const container = document.getElementById("toast-container");

        const toast = document.createElement("div");
        toast.className = `
            flex items-start gap-3 max-w-xs p-4 rounded-2xl bg-blue-100 border border-blue-300 shadow-lg
            animate-slide-in-fade
        `;

        toast.innerHTML = `
            <div class="flex-shrink-0 mt-1 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z" />
                </svg>
            </div>
            <div class="flex-1 text-sm text-blue-800">
                ${message}
            </div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add("opacity-0", "translate-y-4", "transition-all", "duration-500");
            setTimeout(() => toast.remove(), 500);
        }, 6000);
    }

    function playSound() {
        const audio = new Audio('/sounds/notifikasi.wav');
        audio.play();
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (Notification.permission !== "granted") {
            Notification.requestPermission().then(function (permission) {
                if (permission === "granted") {
                    console.log("Notifikasi diizinkan");
                }
            });
        }

        setInterval(cekStatusPesanan, 5000);
    });
</script>

</x-layouts.page>
