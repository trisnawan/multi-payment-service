<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\PaymentGateway;
$paymentGateway = new PaymentGateway();
$data = $paymentGateway->findPaymentMethod();
var_dump($data);