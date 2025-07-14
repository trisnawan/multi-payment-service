<?php

namespace Trisnawan\MultiPaymentService;

class ItemMethod {
    public string|null $id, $title, $channel_id, $fee, $status, $logo;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->channel_id = $data['channel_id'] ?? null;
        $this->fee = $data['fee'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->logo = $data['logo'] ?? null;
    }
}