<?php

namespace Trisnawan\MultiPaymentService;

class ItemTransaction {
    public string|null $id, $user_id, $method_id, $subscription_id;
    public string|null $category_id, $category_reference;
    public string|null $client_code, $provider_code, $type, $status;
    public string|null $buyer_name, $charge_amount, $capture_amount, $fee_amount;
    public string|null $payment_method, $payment_provider;
    public string|null $mobile_number, $cashtag, $return_url;
    public ItemDirectTransaction|null $direct;
    public ItemRedirectTransaction|null $redirect;
    public string|null $expired_at, $created_at, $updated_at;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->category_reference = $data['category_reference'] ?? null;
        $this->method_id = $data['method_id'] ?? null;
        $this->subscription_id = $data['subscription_id'] ?? null;
        $this->client_code = $data['client_code'] ?? null;
        $this->provider_code = $data['provider_code'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->buyer_name = $data['buyer_name'] ?? null;
        $this->charge_amount = $data['charge_amount'] ?? null;
        $this->capture_amount = $data['capture_amount'] ?? null;
        $this->fee_amount = $data['fee_amount'] ?? null;
        $this->payment_method = $data['payment_method'] ?? null;
        $this->payment_provider = $data['payment_provider'] ?? null;
        $this->mobile_number = $data['mobile_number'] ?? null;
        $this->cashtag = $data['cashtag'] ?? null;
        $this->return_url = $data['return_url'] ?? null;

        if (isset($data['direct'])) {
            $this->direct = new ItemDirectTransaction($data['direct']);
        } else {
            $this->direct = null;
        }

        if (isset($data['redirect'])) {
            $this->redirect = new ItemRedirectTransaction($data['redirect']);
        } else {
            $this->redirect = null;
        }

        $this->expired_at = isset($data['expired_at']) ? date('Y-m-d H:i:s', strtotime($data['expired_at'])) : null;
        $this->created_at = isset($data['created_at']) ? date('Y-m-d H:i:s', strtotime($data['created_at'])) : null;
        $this->updated_at = isset($data['updated_at']) ? date('Y-m-d H:i:s', strtotime($data['updated_at'])) : null;
    }
}

class ItemDirectTransaction {
    public $id, $virtual_account, $qr_string;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->virtual_account = $data['virtual_account'] ?? null;
        $this->qr_string = $data['qr_string'] ?? null;
    }
}

class ItemRedirectTransaction {
    public $id, $redirect_url, $redirect_mobile, $redirect_deeplink;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->redirect_url = $data['redirect_url'] ?? null;
        $this->redirect_mobile = $data['redirect_mobile'] ?? null;
        $this->redirect_deeplink = $data['redirect_deeplink'] ?? null;
    }
}