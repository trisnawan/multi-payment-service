<?php

namespace Trisnawan\MultiPaymentService;

/**
 * Class ItemMethod
 * 
 * Response data from Payment Method.
 * Jalankan `new ItemMethod($data)` untuk data single atau jalankan
 * `ItemMethod::fromArrayList($data)` untuk data list.
 */
class ItemMethod {
    public string|null $id, $title, $channel_id, $fee, $status, $logo;
    public ?string $provider_id, $ch_code, $bank_code, $note;
    public ?float $fee_percent;
    public ?int $min_amount, $max_amount;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->channel_id = $data['channel_id'] ?? null;
        $this->fee = $data['fee'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->logo = $data['logo'] ?? null;
        $this->provider_id = $data['provider_id'] ?? null;
        $this->ch_code = $data['ch_code'] ?? null;
        $this->bank_code = $data['bank_code'] ?? null;
        $this->note = $data['note'] ?? null;
        $this->fee_percent = isset($data['fee_percent']) ? (float) $data['fee_percent'] : null;
        $this->min_amount = isset($data['min_amount']) ? (int) $data['min_amount'] : null;
        $this->max_amount = isset($data['max_amount']) ? (int) $data['max_amount'] : null;
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
