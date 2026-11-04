# Multi Payment Library for PHP

Library ini digunakan untuk mengakses backend kanal pembayaran private hosted. Backend saat ini belum dapat saya share secara bebas (hanya untuk penggunaan pribadi).

## Installation

```bash
composer require trisnawan/multi-payment-service
```

## Environment

```plain
payment.api = 'https://baseurl.payment.api/'
payment.token = 'secret-token'
payment.webhook = 'webhook-token'
```

Variabel tetap menggunakan nama yang sama dan dibaca melalui `getenv()`. Muat `.env` seperti pada aplikasi Anda sebelumnya. `payment.api` boleh dengan atau tanpa slash terakhir. `payment.token` adalah bearer token integrasi dari service aktif; `payment.webhook` adalah webhook secret service tersebut.

## Payments

```php
use Trisnawan\MultiPaymentService\RequestPayment;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;
use Trisnawan\MultiPaymentService\PaymentDirect;
use Trisnawan\MultiPaymentService\PaymentInvoice;

$payment = new RequestPayment();
$buyer = new PaymentBuyer('buyer-001', 'Pembeli', 'buyer@example.com', '081234567890');
$category = new PaymentCategory('orders', 'Orders', 'Pembelian produk');

// Direct: pembeli membayar memakai instruksi VA, QR, atau URL provider.
$transaction = $payment->createDirect(new PaymentDirect(
    $buyer, $category, 'method-id', 'order-001',
    'Pesanan', 'Pembelian produk', 10000, 'https://example.com/payment/return'
));

// Payment link: pembeli memilih metode pada halaman checkout.
$invoice = new PaymentInvoice(
    $buyer, $category, 'order-002',
    'Pesanan', 'Pembelian produk', 10000, 'https://example.com/payment/return'
);
// Opsional; tanpa nilai ini backend memakai kedaluwarsa default.
$invoice->expired_at = date('Y-m-d H:i:s', time() + 3600);
$link = $payment->createLink($invoice);
echo $link->link;
```

Pilih ID metode dari `$payment->findPaymentMethod(10000)`. Gunakan reference yang sama untuk retry request yang sama; backend mengembalikan transaksi/invoice sebelumnya. Gunakan reference baru untuk pembayaran baru. Nominal IDR berupa integer.

## Kompatibilitas

Namespace, constructor, parameter, default argument, dan tipe hasil method lama dipertahankan. Aplikasi yang sudah menggunakan `createDirect`, `findDirect`, `getDirect`, `createLink`, `findLink`, `getLink`, `getReport`, `findPaymentMethod`, `findCategories`, `cancelDirect`, `cancelLink`, `refreshDirect`, atau `Webhook::verifyData()` tidak perlu mengubah kode pemanggilannya. Konfigurasi environment juga tetap sama.

Helper menerima response object/array langsung dari endpoint lama serta envelope `{ "status": 200, "data": ... }`. `requestGet()`, `requestPost()`, dan `getData()` tetap memberikan response mentah. `findDirect()` tetap memberikan array `ItemTransaction`, dan `ItemMethod::$fee` tetap string. Properti lama tetap tersedia, termasuk `direct`, `redirect`, `invoice`, `mobile_number`, `cashtag`, dan `return_url`.

Data tambahan yang tersedia bila dikirim backend:

- `ItemTransaction`: `invoice_id`, `provider_id`, `channel_id`, `ch_code`, `bank_code`, judul/deskripsi, informasi metode/pelanggan/kategori, `items`, `properties`, `cycle_number`, dan `event`. Properti `return_url`, `mobile_number`, dan `cashtag` juga membaca objek `properties` dari backend baru. `direct->qr_image` memuat URL gambar QR.
- `ItemInvoice`: `transactions` berupa array `ItemTransaction`, `updated_at`, dan `category_description`.
- `ItemMethod`: `provider_id`, `ch_code`, `bank_code`, `note`, `fee_percent`, `min_amount`, dan `max_amount`. Field opsional yang tidak dikirim backend bernilai null.

## Lifecycle, simulasi, dan laporan

| Method | Hasil |
| --- | --- |
| `getCapabilities()` | Array `simulation` dan `remote_cancellation`. |
| `getTransaction($id)` | `ItemTransaction` untuk transaksi direct maupun redirect. |
| `simulateTransaction($id, $status = 'paid')` | Array `id`, `status`, `changed`. |
| `simulateDirect($id, $status = 'paid')` | Alias `simulateTransaction`. |
| `cancelDirect($id)` | Array hasil pembatalan transaksi lokal. |
| `cancelLink($id)` | Array hasil pembatalan invoice dan attempt unpaid. |
| `refreshDirect($id)` | Array hasil rekonsiliasi status dari provider Pivot. |
| `resendWebhook($id)` | Array `sending` setelah webhook berhasil dikirim. |
| `findReport($filters = [])` | Array `total` dan `data` berupa array `ItemTransaction`. |

```php
$capabilities = $payment->getCapabilities();
if ($capabilities['simulation']) {
    $result = $payment->simulateTransaction($transaction->id, 'paid');
    $latest = $payment->getTransaction($transaction->id);
}

$report = $payment->findReport([
    'limit' => 20, 'offset' => 0, 'status' => 'paid',
    'start' => '2026-01-01', 'end' => '2026-12-31', 'cat_ref' => 'orders',
]);
```

Simulasi hanya tersedia pada backend dengan `CI_ENVIRONMENT` selain `production` (development/testing) dan token integrasi dengan grant `client_update`. Token yang dibuat dashboard sudah memiliki grant read/create/update. Token hanya dapat mengubah transaksi service miliknya. Status yang diterima: `paid`, `expired`, `cancel`, `cancelled`, `failed`; `cancel` disimpan sebagai `cancelled`. Pengulangan status yang sama idempotent, perubahan antarstatus final ditolak HTTP 409. Simulasi memperbarui transaksi/invoice/laporan dan mengantrekan webhook sesuai konfigurasi backend.

Production menolak simulasi HTTP 403. Token tanpa izin update ditolak 403; akses di luar service ditolak 404; token dicabut/kedaluwarsa atau service nonactive ditolak 401. Flag development di aplikasi pemakai tidak dapat mengaktifkan simulasi pada backend production.

Pembatalan bersifat lokal; tagihan provider mungkin tetap aktif (`remote_cancellation=false`). Callback paid yang terverifikasi tetap direkonsiliasi sesudah pembatalan/expiry. `findReport()` mendukung filter backend `limit`, `offset`, `status`, `search`, `start`, `end`, `cat_id`, `cat_ref`, dan `client_id` sesuai scope token.

Kegagalan API tetap melempar `\Exception`; `getCode()` sekarang berisi status HTTP untuk membedakan 403/409/502. `getResponseCode()`, `getData()`, dan `isSuccess()` mengikuti request terakhir, termasuk setelah error. Kegagalan koneksi mempertahankan exception Guzzle.

## Webhook

```php
use Trisnawan\MultiPaymentService\Webhook;

$webhook = new Webhook();
try {
    $transaction = $webhook->verifyData();
    // Deduplikasi berdasarkan $transaction->id + $transaction->status.
    // Gunakan client_code untuk menghubungkan transaksi ke order aplikasi.
    http_response_code(200);
} catch (\Exception $error) {
    http_response_code($webhook->responseCode);
}
```

`verifyData()` tetap membaca raw body dan header request. Untuk framework yang menyediakan keduanya, tersedia `$webhook->verifyPayload($rawBody, $headers)`, dengan hasil `ItemTransaction` yang sama. Jangan decode/re-encode body sebelum verifikasi. Signature HMAC-SHA256 diverifikasi dengan `hash_equals`; nama header tidak membedakan huruf besar/kecil. Jika `M-Payment-Token` dikirim, nilainya juga harus sesuai secret. Webhook lama yang hanya membawa signature tetap didukung. Secret kosong ditolak.

Backend mengirim event `transactions` dengan status `unpaid`, `paid`, `expired`, `cancelled`, atau `failed`. Pengiriman dapat berulang; jangan menurunkan status paid karena callback lama.

## Pengujian

```bash
composer test
```

Tes regresi memakai Guzzle MockHandler dan tidak membaca `.env` atau menghubungi provider. Untuk pengujian API sebenarnya, jalankan `python tests/integration/verify.py` dari direktori `payment-service-php`; runner membuat database disposable dan memanggil `tests/service.php` library pada development/testing/production. Lihat README pengujian backend untuk prasyaratnya.
