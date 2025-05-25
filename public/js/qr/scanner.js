window.addEventListener('load', function () {
  navigator.mediaDevices.getUserMedia({ video: {facingMode: "environment"} })
      .then(function (stream) {
        let html5QRCodeScanner = new Html5QrcodeScanner(
            "reader", {
                fps: 10,
                qrbox: { width: 300, height: 300 },
                aspectRatio: /Mobi/i.test(window.navigator.userAgent) ? 16 / 9 : 9 / 16,
                facingMode: "environment"
            }
        );

        function onScanSuccess(decodedText, decodedResult) {
            fetch('/store-qr-result', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    table_number: decodedText.split('/')[1]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = "/";
                } else {
                    console.error("Failed to store QR result", data);
                }
                html5QRCodeScanner.clear();
                stream.getTracks().forEach(track => track.stop());
            })
            .catch(error => {
                console.error('Error sending QR data:', error);
                alert("An error occurred while processing the QR code.");
            });
        }
    
        html5QRCodeScanner.render(onScanSuccess);
      })
      .catch(function (err) {
          console.error("Izin akses kamera ditolak: ", err);
          alert("Izin akses kamera dibutuhkan untuk scan kode QR.");
      });
});