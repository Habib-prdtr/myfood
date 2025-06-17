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
            setTimeout(() => {
                toast.remove();
                location.reload();
            }, 500);
        }, 5000);
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
            setTimeout(() => {
                toast.remove();
                location.reload();
            }, 500);
        }, 5000);
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
