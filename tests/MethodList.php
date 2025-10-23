<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\RequestPayment;
$payment = new RequestPayment();

$data = $payment->findPaymentMethod(1000000);
// $data = $payment->findCategories();
var_dump($data);