<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use App\Models\Category;
use App\Models\Foods;
use App\Models\Transaction;
use App\Models\TransactionItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class TransactionController extends Controller
{
    protected $apiInstance;

    public function __construct()
    {
        $apiKey = config('xendit.secret_key');

        $client = new \GuzzleHttp\Client([
            'auth' => [$apiKey, ''],
        ]);

        $this->apiInstance = new InvoiceApi($client);
    }

    public function handlePayment(Request $request, $token)
    {
        Log::info('MASUK KE handlePayment', [
            'token' => $token,
            'action' => $request->input('action'),
        ]);

        $action = $request->input('action');

        if ($action === 'pay') {
            return $this->processPayment($request, $token);
        }

        if ($action === 'continue') {
            $externalId = session('external_id');

            if (empty($externalId)) {
                return view('payment.failure');
            }

            $transaction = Transaction::where('external_id', $externalId)->first();
            if (!$transaction) {
                return view('payment.failure');
            }

            return redirect($transaction->checkout_link);
        }

        abort(400, 'Invalid action.');
    }

    public function processPayment(Request $request, $token)
    {
        
        $uuid = (string) Str::uuid();

        $sessionToken = session('payment_token');
        $requestToken = $token;

        if ($sessionToken !== $requestToken) {
            return redirect()->route('payment.failure');
        }

        $cartItems = session('cart_items');
        $name = session('name');
        $phone = session('phone');
        $tableNumber = session('table_number');

        if (empty($cartItems) || empty($name) || empty($phone) || empty($tableNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'Data is empty',
            ], 400);
        }

        $tableNumberRecord = Barcode::where('table_number', $tableNumber)->first();
        if (!$tableNumberRecord) {
            // Nomor meja tidak ditemukan
            Log::warning("Nomor meja tidak ditemukan: {$tableNumber}");
            return redirect()->route('payment.failure')->with('error', 'Nomor meja tidak ditemukan.');
        }

        $transactionCode = 'TRX_' . mt_rand(100000, 999999);

        try {
            $subTotal = 0;
            $items = collect($cartItems)
                ->map(function ($item) use (&$subTotal) {
                    $price = isset($item['price_afterdiscount']) ? $item['price_afterdiscount'] : $item['price'];
                    $category = Category::find($item['categories_id'])->name ?? 'Lainnya';
                    $foodSubtotal = $price * $item['quantity'];
                    $subTotal += $foodSubtotal;

                    $url = route('product.detail', ['id' => $item['id']]);

                    return [
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => (int) $price,
                        'category' => $category,
                        'url' => $url,
                    ];
                })
                ->values()
                ->toArray();

            $ppn = round(0.11 * $subTotal);

            $description = <<<END
            Pembayaran makanan<br>
            Nomor Meja: {$tableNumber}<br>
            Nama: {$name}<br>
            Nomor Telepon: {$phone}<br>
            Kode Transaksi: {$transactionCode}<br>
            END;

            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $uuid,
                'amount' => $subTotal + $ppn,
                'description' => $description,
                'invoice_duration' => 3600, // 1 jam
                'currency' => 'IDR',
                'customer' => [
                    'given_names' => $name,
                    'mobile_number' => $phone,
                ],
                'success_redirect_url' => route('payment.success'),
                'failure_redirect_url' => route('payment.failure'),
                'locale' => 'id',
                'items' => $items,
                'fees' => [
                    [
                        'type' => 'PPN 11%',
                        'value' => $ppn,
                    ],
                ],
                'customer_notification_preference' => [
                    'invoice_paid' => [
                        'whatsapp',
                    ],
                ],
            ]);

            $invoice = $this->apiInstance->createInvoice($createInvoiceRequest);

            $transaction = new Transaction();
            $transaction->checkout_link = $invoice['invoice_url'] ?? '';
            $transaction->payment_method = "CREDIT CARD";
            $transaction->phone = $phone;
            $transaction->name = $name;
            $transaction->subtotal = $subTotal;
            $transaction->ppn = $ppn;
            $transaction->barcodes_id = $tableNumberRecord->id;
            $transaction->total = $subTotal + $ppn;
            $transaction->external_id = $uuid;
            $transaction->code = $transactionCode;
            $transaction->payment_status = "SUCCESS";
            $transaction->save();

            foreach ($cartItems as $cartItem) {
                $price = isset($cartItem['price_afterdiscount']) ? $cartItem['price_afterdiscount'] : $cartItem['price'];

                TransactionItems::create([
                    'transaction_id' => $transaction->id,
                    'foods_id' => $cartItem['id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $price,
                    'subtotal' => $price * $cartItem['quantity'],
                ]);
            }

            session([
                'id_transaksi' => $transaction->id,
                'nama' => $transaction->name,
            ]);

            session(['external_id' => $uuid]);
            session(['has_unpaid_transaction' => true]);

            return redirect($invoice['invoice_url'] ?? route('payment.failure'));

        } catch (\Exception $e) {
            Log::error('Failed to create invoice', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('payment.failure');
        }
    }

    public function clearSession()
    {
        Session::forget(['name', 'external_id', 'has_unpaid_transaction', 'cart_items', 'payment_token']);
        Session::save();
    }

    public function pesananSaya()
    {
        $nama = session('nama');

        $orders = \App\Models\Transaction::with(['Items.food'])
            ->where('name', $nama)
            ->latest()
            ->get();

        return view('pesanan', compact('orders'));
    }

    public function cekStatusPesanan()
    {
        $nama = session('nama');
        Log::info("Nama dari session:", ['nama' => $nama]);

        // Ambil pesanan terbaru user ini
        $pesanan = \App\Models\Transaction::where('name', $nama)
            ->latest()
            ->first();

        if (!$pesanan) {
            Log::info('Tidak ada pesanan ditemukan.');
            return response()->json([]);
        }

        $statusSekarang = $pesanan->status_pesanan;
        $statusTerakhir = session('status_terakhir');

        Log::info('Status sekarang:', ['status' => $statusSekarang]);
        Log::info('Status terakhir di session:', ['status' => $statusTerakhir]);

        // Jika status berubah, reset notifikasi
        if ($statusSekarang !== $statusTerakhir) {
            $pesanan->notifikasi_terkirim = false;
            $pesanan->save();
            session(['status_terakhir' => $statusSekarang]); // update session
            Log::info('Status berubah, reset notifikasi dan update status_terakhir.');
        }

        // Kalau belum kirim notifikasi untuk status ini
        if (!$pesanan->notifikasi_terkirim && in_array($statusSekarang, ['diproses', 'diantar'])) {
            $pesanan->notifikasi_terkirim = true;
            $pesanan->save();

            Log::info('Kirim notifikasi:', ['status' => $statusSekarang]);

            return response()->json([
                'status' => $statusSekarang,
                'nama' => $pesanan->name,
            ]);
        }

        return response()->json([]);
    }


//     public function paymentStatus($id)
//     {
//         // Contoh implementasi cek status pembayaran
//         $transaction = Transaction::where('external_id', $id)->first();

//         if (!$transaction) {
//             return response()->json(['message' => 'Transaction not found'], 404);
//         }

//         return response()->json([
//             'status' => $transaction->payment_status,
//             'external_id' => $transaction->external_id,
//             'total' => $transaction->total,
//         ]);
//     }

//     public function handleWebhook(Request $request)
// {
    
//     Log::info('Webhook diterima', ['payload' => $request->all()]);

//     $externalId = $request->input('external_id');
//     $status = $request->input('status');
//     $paymentMethod = $request->input('payment_method');

//     $transaction = Transaction::where('external_id', $externalId)->first();

//     if (! $transaction) {
//         Log::warning("Transaksi tidak ditemukan untuk external_id: $externalId");
//         return response()->json(['message' => 'Transaction not found'], 404);
//     }

//     $transaction->payment_status = $status;
//     $transaction->payment_method = $paymentMethod;
//     $transaction->save();

//     Log::info('Transaksi diperbarui dari webhook', [
//         'external_id' => $externalId,
//         'status' => $status,
//         'payment_method' => $paymentMethod,
//     ]);

//     return response()->json(['message' => 'Webhook processed'], 200);
// }

}
