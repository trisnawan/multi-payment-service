<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\ItemInvoice;

/**
 * Class ItemTransaction
 * 
 * Response data from Transaction.
 * Jalankan `new ItemTransaction($data)` untuk data single atau jalankan
 * `ItemTransaction::fromArrayList($data)` untuk data list.
 */
class ItemTransaction {
    public string|null $id, $user_id, $method_id, $subscription_id;
    public string|null $category_id, $category_reference;
    public string|null $client_code, $provider_code, $type, $status;
    public int $charge_amount, $capture_amount, $fee_amount, $total_amount;
    public string|null $buyer_name, $payment_method, $payment_provider;
    public string|null $mobile_number, $cashtag, $return_url;
    public ItemDirectTransaction|null $direct;
    public ItemRedirectTransaction|null $redirect;
    public ItemInvoice|null $invoice;
    public string|null $expired_at, $created_at, $updated_at;
    public ?string $invoice_id, $provider_id, $channel_id, $ch_code, $bank_code;
    public ?string $title, $description, $payment_note, $payment_logo;
    public ?string $category_title, $category_description, $app_title;
    public ?string $user_full_name, $user_email, $user_phone, $event;
    public ?int $cycle_number;
    public array $properties, $items;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->category_reference = $data['category_reference'] ?? null;
        $this->method_id = $data['method_id'] ?? null;
        $this->subscription_id = $data['subscription_id'] ?? null;
        $this->invoice_id = $data['invoice_id'] ?? null;
        $this->provider_id = $data['provider_id'] ?? null;
        $this->channel_id = $data['channel_id'] ?? null;
        $this->ch_code = $data['ch_code'] ?? null;
        $this->bank_code = $data['bank_code'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->payment_note = $data['payment_note'] ?? null;
        $this->payment_logo = $data['payment_logo'] ?? null;
        $this->category_title = $data['category_title'] ?? null;
        $this->category_description = $data['category_description'] ?? null;
        $this->app_title = $data['app_title'] ?? null;
        $this->user_full_name = $data['user_full_name'] ?? null;
        $this->user_email = $data['user_email'] ?? null;
        $this->user_phone = $data['user_phone'] ?? null;
        $this->event = $data['event'] ?? null;
        $this->cycle_number = isset($data['cycle_number']) ? (int) $data['cycle_number'] : null;
        $this->properties = $data['properties'] ?? [];
        $this->items = $data['items'] ?? [];
        $this->client_code = $data['client_code'] ?? null;
        $this->provider_code = $data['provider_code'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->buyer_name = $data['buyer_name'] ?? null;
        $this->charge_amount = $data['charge_amount'] ?? 0;
        $this->capture_amount = $data['capture_amount'] ?? 0;
        $this->fee_amount = $data['fee_amount'] ?? 0;
        $this->total_amount = $data['total_amount'] ?? 0;
        $this->payment_method = $data['payment_method'] ?? null;
        $this->payment_provider = $data['payment_provider'] ?? null;
        $this->mobile_number = $data['mobile_number'] ?? $this->properties['mobile_number'] ?? null;
        $this->cashtag = $data['cashtag'] ?? $this->properties['cashtag'] ?? null;
        $this->return_url = $data['return_url'] ?? $this->properties['return_url'] ?? null;

        $this->direct = new ItemDirectTransaction($data['direct'] ?? null);
        $this->redirect = new ItemRedirectTransaction($data['redirect'] ?? null);
        $this->invoice = new ItemInvoice($data['invoice'] ?? null);

        $this->expired_at = isset($data['expired_at']) ? date('Y-m-d H:i:s', strtotime($data['expired_at'])) : null;
        $this->created_at = isset($data['created_at']) ? date('Y-m-d H:i:s', strtotime($data['created_at'])) : null;
        $this->updated_at = isset($data['updated_at']) ? date('Y-m-d H:i:s', strtotime($data['updated_at'])) : null;
    }

    public static function fromArrayList(array $data): array{
        if(!isset($data[0]['id'])) [];
        
        $invoices = [];
        foreach($data as $val){
            $invoices[] = new self($val);
        }
        return $invoices;
    }
}

class ItemDirectTransaction {
    public $id, $virtual_account, $qr_string, $qr_image;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->virtual_account = $data['virtual_account'] ?? null;
        $this->qr_string = $data['qr_string'] ?? null;
        $this->qr_image = $data['qr_image'] ?? null;
    }
}

class ItemRedirectTransaction {
    public $id, $redirect_url, $redirect_mobile, $redirect_deeplink;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->redirect_url = $data['redirect_url'] ?? null;
        $this->redirect_mobile = $data['redirect_mobile'] ?? null;
        $this->redirect_deeplink = $data['redirect_deeplink'] ?? null;
    }
}
