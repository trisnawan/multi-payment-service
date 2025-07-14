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
```

## Initialization

```php
use Trisnawan\MultiPaymentService\PaymentGateway;
```

## List Payment Method

```php
$payment = new PaymentGateway();
$data = $payment->findPaymentMethod();
```

## List Categories

```php
$payment = new PaymentGateway();
$data = $payment->findCategories();
```

## Create Invoice

```php
$invoice = new PaymentInvoice("Title", "Description", 100000, "https://redirect.link");
$buyer = new PaymentBuyer("Full name", "email@example.com", "+628xxx");
$category = new PaymentCategory("id-cat-1", "Category Title", "Category Description");

$payment = new PaymentGateway();
$data = $payment->createTransaction($method_id, $client_code, $invoice, $buyer, $category);
```

## Get Detail Invoice

```php
$payment = new PaymentGateway();
$data = $payment->getTransaction($id);
```
