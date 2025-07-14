<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\PaymentGateway;
$paymentGateway = new PaymentGateway();
$data = $paymentGateway->getTransaction("01980909-da43-73a3-9040-49fad9c6e84f");
var_dump($data);