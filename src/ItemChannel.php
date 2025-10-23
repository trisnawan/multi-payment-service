<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\ItemMethod;

/**
 * Class ItemChannel
 * 
 * Response data from Payment Channel.
 * Jalankan `new ItemChannel($data)` untuk data single atau jalankan
 * `ItemChannel::fromArrayList($data)` untuk data list.
 */
class ItemChannel {
    public string|null $id, $title;
    public array $items;

    public function __construct(array|null $data = null) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->items = ItemMethod::fromArrayList(is_array($data['items']??null) ? $data['items'] : []);
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