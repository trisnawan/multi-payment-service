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

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->channel_id = $data['channel_id'] ?? null;
        $this->fee = $data['fee'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->logo = $data['logo'] ?? null;
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