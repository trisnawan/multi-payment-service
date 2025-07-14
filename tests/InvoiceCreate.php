<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\PaymentGateway;
$paymentGateway = new PaymentGateway();
$data = $paymentGateway->createTransaction(
    "eb451b46-3c01-49b8-a859-206f8cd5f34d",
    "testing-invoice-250714001",
    new \Trisnawan\MultiPaymentService\PaymentInvoice("Test Transaction", "This is a test transaction", 10000, "https://example.com/redirect"),
    new \Trisnawan\MultiPaymentService\PaymentBuyer("User Full Name", "email@example.com", "081234567890"),
    new \Trisnawan\MultiPaymentService\PaymentCategory("testing-invoice", "Test Invoice", "This is a test category"));
var_dump($data);