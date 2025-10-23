<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../');
$dotenv->load();

use Trisnawan\MultiPaymentService\RequestPayment;
use Trisnawan\MultiPaymentService\PaymentInvoice;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;

$payment = new RequestPayment();

// $data = $payment->createLink(new PaymentInvoice(
//     new PaymentBuyer("100", "Trisnawan", "halo.trisnasejati@gmail.com", "+6287719734045"),
//     new PaymentCategory("event", "Moneesa Event", "Moneesa Event Testing"),
//     "df2103ecbba8817c446a3a5338fe3f01",
//     "Kelas Testing RFA",
//     "Workshop Registerede Financial Associate",
//     1000000,
//     "https://moneesa.com/widget/event/invoice/df2103ecbba8817c446a3a5338fe3fa6"
// ));
$data = $payment->findLink();
// $data = $payment->getLink("019a1045-c102-7082-a2a5-9a37af5eba58");
var_dump($data);