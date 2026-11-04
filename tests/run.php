<?php

// Offline regression suite; no .env, real API, or provider charges.
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Trisnawan\MultiPaymentService\ItemInvoice;
use Trisnawan\MultiPaymentService\ItemTransaction;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;
use Trisnawan\MultiPaymentService\PaymentDirect;
use Trisnawan\MultiPaymentService\PaymentInvoice;
use Trisnawan\MultiPaymentService\RequestPayment;
use Trisnawan\MultiPaymentService\Webhook;

class MockPayment extends RequestPayment {
    public function __construct(array $responses, array &$history) {
        parent::__construct();
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));
        $this->client = new Client(['handler' => $stack]);
    }
}

function same($expected, $actual): void {
    if($expected !== $actual) throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
}

function fails(callable $call, int $code): void {
    try { $call(); } catch(Exception $error) { same($code, $error->getCode()); return; }
    throw new RuntimeException('Expected an exception');
}

function response(array $data, int $code = 200): Response {
    return new Response($code, ['Content-Type' => 'application/json'], json_encode($data));
}

$tests = [];
$tests['existing constructors and payment methods keep their contracts'] = function () {
    $history = [];
    $transaction = ['id' => 'tx', 'status' => 'unpaid', 'charge_amount' => '10000', 'direct' => ['virtual_account' => '123']];
    $invoice = ['id' => 'invoice', 'client_code' => 'order-1', 'amount' => '10000', 'link' => 'https://pay.test/link/invoice', 'reference_id' => 'buyer-1', 'full_name' => 'Buyer'];
    $payment = new MockPayment([
        response([['id' => 'va', 'title' => 'VA', 'items' => [['id' => 'method', 'fee' => 2500, 'provider_id' => 'pivot', 'fee_percent' => '0.7000']]]]),
        response([['id' => 'cat', 'title' => 'Orders']]),
        response($invoice), response([$invoice]), response($invoice),
        response($transaction), response(['total' => 1, 'data' => [$transaction]]), response($transaction), response($transaction),
        response(['status' => 200, 'data' => ['id' => 'tx', 'status' => 'cancelled', 'changed' => true]]),
        response(['status' => 200, 'data' => ['id' => 'invoice', 'status' => 'cancelled', 'changed' => true]]),
        response(['status' => 200, 'data' => ['id' => 'tx', 'status' => 'paid']]),
    ], $history);
    $buyer = new PaymentBuyer('buyer-1', 'Buyer', 'buyer@example.test', '081234567890');
    $category = new PaymentCategory('orders', 'Orders', 'Order payments');
    $link = new PaymentInvoice($buyer, $category, 'order-1', 'Order', 'Description', 10000, 'https://shop.test/return');
    $direct = new PaymentDirect($buyer, $category, 'method', 'order-1', 'Order', 'Description', 10000, 'https://shop.test/return');
    $method = $payment->findPaymentMethod(10000)[0]->items[0];
    same('2500', $method->fee); // Existing string property stays a string.
    same('pivot', $method->provider_id);
    same(0.7, $method->fee_percent);
    same('cat', $payment->findCategories()[0]->id);
    same('invoice', $payment->createLink($link)->id);
    same('invoice', $payment->findLink(10, 0, 'Order')[0]->id);
    same('buyer-1', $payment->getLink('invoice')->buyer->reference_id);
    same('tx', $payment->createDirect($direct)->id);
    same('tx', $payment->findDirect(10, 0, 'unpaid', 'Order')[0]->id);
    same('123', $payment->getDirect('tx')->direct->virtual_account);
    same(10000, $payment->getReport('tx')->charge_amount);
    same('cancelled', $payment->cancelDirect('tx')['status']);
    same('cancelled', $payment->cancelLink('invoice')['status']);
    same('paid', $payment->refreshDirect('tx')['status']);
    same(true, $payment->isSuccess());
    same(200, $payment->getResponseCode());
    same(['status' => 200, 'data' => ['id' => 'tx', 'status' => 'paid']], $payment->getData());
    $sent = json_decode((string) $history[5]['request']->getBody(), true);
    same('order-1', $sent['client_code']);
    same('buyer-1', $sent['buyer_id']);
    same('orders', $sent['category']['reference_id']);
};

$tests['new helpers use scoped endpoints and preserve raw transport responses'] = function () {
    $history = [];
    $capabilities = ['simulation' => true, 'remote_cancellation' => false];
    $payment = new MockPayment([
        response(['status' => 200, 'data' => $capabilities]),
        response(['status' => 200, 'data' => ['id' => 'tx', 'status' => 'unpaid']]),
        response(['status' => 200, 'data' => ['id' => 'tx', 'status' => 'paid', 'changed' => true]]),
        response(['id' => 'tx', 'status' => 'cancelled', 'changed' => true]),
        response(['status' => 200, 'data' => ['sending' => true]]),
        response(['total' => 3, 'data' => [['id' => 'tx', 'status' => 'expired']]]),
    ], $history);
    same($capabilities, $payment->getCapabilities());
    same(['status' => 200, 'data' => $capabilities], $payment->getData());
    same('tx', $payment->getTransaction('tx/a b')->id);
    same(true, $payment->simulateTransaction('tx')['changed']);
    same('cancelled', $payment->simulateDirect('tx', 'cancel')['status']);
    same(['sending' => true], $payment->resendWebhook('tx'));
    $report = $payment->findReport(['status' => 'expired', 'cat_ref' => 'orders', 'start' => '2026-01-01']);
    same(3, $report['total']);
    same('expired', $report['data'][0]->status);
    same('/api/payment/transaction/detail/tx%2Fa%20b', $history[1]['request']->getUri()->getPath());
    same(['status' => 'paid'], json_decode((string) $history[2]['request']->getBody(), true));
    same(['status' => 'cancel'], json_decode((string) $history[3]['request']->getBody(), true));
    same('Bearer test-token', $history[0]['request']->getHeaderLine('Authorization'));
    same('status=expired&cat_ref=orders&start=2026-01-01', $history[5]['request']->getUri()->getQuery());
};

$tests['both bare and wrapped legacy data hydrate the same objects'] = function () {
    $history = [];
    $payment = new MockPayment([
        response(['status' => 200, 'data' => ['id' => 'invoice', 'status' => 'unpaid']]),
        response(['status' => 200, 'data' => ['total' => 1, 'data' => [['id' => 'tx']]]]),
        response(['status' => 200, 'data' => []]), response([]),
    ], $history);
    same('invoice', $payment->getLink('invoice')->id);
    same('tx', $payment->findDirect()[0]->id);
    same([], $payment->findLink());
    same([], $payment->findCategories());
    $item = new ItemTransaction(['id' => 'tx', 'invoice_id' => 'invoice', 'provider_id' => 'pivot',
        'properties' => ['return_url' => 'https://shop.test/return', 'mobile_number' => '08123'],
        'direct' => ['qr_string' => 'QR', 'qr_image' => 'https://pay.test/qr.jpg'], 'items' => [['title' => 'Order']]]);
    same('https://shop.test/return', $item->return_url);
    same('08123', $item->mobile_number);
    same('https://pay.test/qr.jpg', $item->direct->qr_image);
    same('Order', $item->items[0]['title']);
    same(null, (new ItemTransaction())->id);
    same([], (new ItemInvoice())->transactions);
    $invoice = new ItemInvoice(['transactions' => [['id' => 'attempt']], 'buyer' => ['reference_id' => 'buyer']]);
    same('attempt', $invoice->transactions[0]->id);
    same('buyer', $invoice->buyer->reference_id);
};

$tests['errors reset state and expose API conflict and forbidden codes'] = function () {
    $history = [];
    $payment = new MockPayment([
        response(['ok' => true]), response(['messages' => ['error' => 'Production disabled']], 403),
        response(['messages' => ['error' => 'Already final']], 409), new Response(502, [], '<html>Bad gateway</html>'),
        new Response(200, [], 'bad json'), response(['ok' => true]),
        new ConnectException('Offline', new Request('GET', 'https://payment.test')),
    ], $history);
    $payment->requestGet('payment/method?amount=1', ['offset' => 2]);
    same('amount=1&offset=2', $history[0]['request']->getUri()->getQuery());
    fails(function () use ($payment) { $payment->simulateDirect('tx'); }, 403);
    same(false, $payment->isSuccess());
    same(403, $payment->getResponseCode());
    fails(function () use ($payment) { $payment->simulateDirect('tx'); }, 409);
    fails(function () use ($payment) { $payment->requestGet('payment/method'); }, 502);
    same(null, $payment->getData());
    fails(function () use ($payment) { $payment->requestGet('payment/method'); }, 200);
    same(false, $payment->isSuccess());
    $payment->requestGet('payment/method');
    fails(function () use ($payment) { $payment->requestGet('payment/method'); }, 0);
    same(null, $payment->getResponseCode());
    same(null, $payment->getData());
    same(false, $payment->isSuccess());
};

$tests['webhook accepts legacy signatures and current headers using the raw body'] = function () {
    $webhook = new Webhook();
    $body = '{"event":"transactions","id":"tx","client_code":"order-1","status":"paid","charge_amount":10000}';
    $sign = function ($body) { return hash_hmac('sha256', hash('sha256', $body) . ':test-secret', 'test-secret'); };
    same('paid', $webhook->verifyPayload($body, ['Signature' => $sign($body)])->status);
    same('transactions', $webhook->verifyPayload($body, ['SIGNATURE' => $sign($body), 'M-PAYMENT-TOKEN' => 'test-secret'])->event);
    same(200, $webhook->responseCode);
    fails(function () use ($webhook, $body, $sign) { $webhook->verifyPayload($body . ' ', ['signature' => $sign($body)]); }, 0);
    same(403, $webhook->responseCode);
    fails(function () use ($webhook, $body, $sign) { $webhook->verifyPayload($body, ['signature' => $sign($body), 'M-Payment-Token' => 'wrong']); }, 0);
    fails(function () use ($webhook) { $webhook->verifyPayload('', []); }, 0);
    same(400, $webhook->responseCode);
    fails(function () use ($webhook, $sign) { $webhook->verifyPayload('null', ['signature' => $sign('null')]); }, 0);
    same(400, $webhook->responseCode);
    putenv('payment.webhook=');
    fails(function () use ($webhook, $body, $sign) { $webhook->verifyPayload($body, ['signature' => $sign($body)]); }, 0);
    same(500, $webhook->responseCode);
};

$tests['empty successful responses and trailing slashes remain supported'] = function () {
    $history = [];
    putenv('payment.api=https://payment.test/api/');
    $payment = new MockPayment([new Response(204)], $history);
    same(null, $payment->requestPost('/payment/example', []));
    same(true, $payment->isSuccess());
    same(204, $payment->getResponseCode());
    same('/api/payment/example', $history[0]['request']->getUri()->getPath());
};

$previous = [];
foreach(['payment.api' => 'https://payment.test/api', 'payment.token' => 'test-token', 'payment.webhook' => 'test-secret'] as $key => $value){
    $previous[$key] = getenv($key);
    putenv($key . '=' . $value);
}
try {
    foreach($tests as $name => $test){
        $test();
        echo 'PASS: ' . $name . PHP_EOL;
    }
    echo count($tests) . ' regression groups passed.' . PHP_EOL;
} finally {
    foreach($previous as $key => $value) putenv($value === false ? $key : $key . '=' . $value);
}
