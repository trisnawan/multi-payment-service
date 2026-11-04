<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\PaymentBuyer;

/**
 * Class ItemInvoice
 * 
 * Response data from Invoice Payment Link.
 * Jalankan `new ItemInvoice($data)` untuk data single atau jalankan
 * `ItemInvoice::fromArrayList($data)` untuk data list.
 */
class ItemInvoice {
    public string|null $id, $user_id;
    public string|null $category_id, $category_reference, $category_title;
    public string|null $client_code, $title, $description;
    public int|null $amount;
    public string|null $link;
    public PaymentBuyer|null $buyer;
    public string|null $redirect_url, $status, $expired_at, $created_at;
    public ?string $updated_at, $category_description;
    /** @var ItemTransaction[] Percobaan pembayaran invoice. */
    public array $transactions;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->category_id = $data['category_id'] ?? null;
        $this->category_reference = $data['category_reference'] ?? null;
        $this->category_title = $data['category_title'] ?? null;
        $this->category_description = $data['category_description'] ?? null;
        $this->client_code = $data['client_code'] ?? $data['reference_id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->redirect_url = $data['redirect_url'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->link = $data['link'] ?? null;
        $buyer = $data['buyer'] ?? [];
        $this->buyer = new PaymentBuyer(
            $data['buyer_id'] ?? $buyer['reference_id'] ?? $data['reference_id'] ?? null,
            $data['buyer_name'] ?? $buyer['full_name'] ?? $data['full_name'] ?? null,
            $data['buyer_email'] ?? $buyer['email'] ?? $data['email'] ?? null,
            $data['buyer_phone'] ?? $buyer['phone'] ?? $data['phone'] ?? null
        );
        $this->transactions = ItemTransaction::fromArrayList($data['transactions'] ?? []);
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
