<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\RequestPayment;
use Trisnawan\MultiPaymentService\PaymentDirect;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;

$payment = new RequestPayment();

// $data = $payment->createDirect(new PaymentDirect(
//     new PaymentBuyer("100", "Trisnawan", "halo.trisnasejati@gmail.com", "+6287719734045"),
//     new PaymentCategory("event", "Moneesa Event", "Moneesa Event Testing"),
//     "b03acc83-4ee4-464c-9adc-928a4aa617f0",
//     "df2103ecbba8817c446a3a5338fe3f02",
//     "Kelas Testing RFA",
//     "Workshop Registerede Financial Associate",
//     1000000,
//     "https://moneesa.com/widget/event/invoice/df2103ecbba8817c446a3a5338fe3fa6"
// ));
// $data = $payment->findDirect();
$data = $payment->getDirect("0198a769-345d-7357-acb1-9f06ad9b8af4");
var_dump($data);