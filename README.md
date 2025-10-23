# Multi Payment Library for PHP

Library ini digunakan untuk mengakses backend kanal pembayaran private hosted. Backend saat ini belum dapat saya share secara bebas (hanya untuk penggunaan pribadi).

## Instalation

```bash
composer require trisnawan/multi-payment-service
```

## Environment

```plain
payment.api = 'https://baseurl.payment.api/'
payment.token = 'secret-token'
payment.webhook = 'webhook-token'
```

## Payments

```php
use Trisnawan\MultiPaymentService\RequestPayment;
```

## Webhook

```php
use Trisnawan\MultiPaymentService\Webhook;
```
