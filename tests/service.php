<?php

// Invoked by the backend's disposable database integration runner. Never loads .env.
require __DIR__ . '/../vendor/autoload.php';

use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;
use Trisnawan\MultiPaymentService\PaymentDirect;
use Trisnawan\MultiPaymentService\PaymentInvoice;
use Trisnawan\MultiPaymentService\RequestPayment;
use Trisnawan\MultiPaymentService\Webhook;

function check($condition): void {
    if(!$condition) throw new RuntimeException('PHP client integration assertion failed');
}

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$ids = $input['ids'];
$environment = $input['environment'];
$payment = new RequestPayment();
check($payment->getCapabilities()['simulation'] === ($environment !== 'production'));
$id = $ids["simulation-$environment-integration-paid"];
if($environment === 'production'){
    try {
        $payment->simulateTransaction($id);
        throw new RuntimeException('Production simulation must be forbidden');
    } catch(Exception $error){
        check($error->getCode() === 403 && !$payment->isSuccess());
    }
    check($payment->getTransaction($id)->status === 'unpaid');
}else{
    check($payment->simulateDirect($id)['changed'] === false);
    check($payment->getTransaction($id)->status === 'paid');
    check($payment->getDirect($id)->id === $id);
    check($payment->getReport($id)->id === $id);
    check(count($payment->findDirect(10, 0, 'paid')) > 0);
    check($payment->findReport(['status' => 'paid', 'cat_ref' => 'test'])['total'] >= 1);
    check(count($payment->findCategories()) > 0);
    check($payment->findPaymentMethod(10000)[0]->items[0]->provider_id === 'pivot');
    $buyer = new PaymentBuyer('sdk-buyer', 'SDK Buyer', 'sdk@example.test', '081234567890');
    $category = new PaymentCategory('sdk', 'SDK', 'SDK regression');
    // Reuse an existing reference; the backend must return it without charging a provider.
    $direct = new PaymentDirect($buyer, $category, $ids['method'], "simulation-$environment-integration-paid",
        'SDK order', 'SDK order', 10000, 'https://example.test/return');
    check($payment->createDirect($direct)->id === $id);
    $invoice = new PaymentInvoice($buyer, $category, 'sdk-' . $environment,
        'SDK invoice', 'SDK invoice', 10000, 'https://example.test/return');
    $invoice->expired_at = '2099-01-01 00:00:00';
    $created = $payment->createLink($invoice);
    check($created->id !== null && $created->link !== null);
    check($payment->createLink($invoice)->id === $created->id);
    check($payment->getLink($created->id)->buyer->reference_id === 'sdk-buyer');
    check(count($payment->findLink(10, 0, 'SDK invoice')) > 0);
    check($payment->cancelLink($created->id)['status'] === 'cancelled');
    $body = $input['webhook'];
    $signature = hash_hmac('sha256', hash('sha256', $body) . ':test-webhook-secret', 'test-webhook-secret');
    $webhook = (new Webhook())->verifyPayload($body, ['signature' => $signature, 'M-Payment-Token' => 'test-webhook-secret']);
    check($webhook->id === $id && $webhook->event === 'transactions' && $webhook->status === 'paid');
}
echo 'PHP client passed: ' . $environment;
