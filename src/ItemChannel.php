<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\ItemMethod;

class ItemChannel {
    public string|null $id, $title;
    public array $items;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->items = [];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->items[] = new ItemMethod($item);
            }
        }
    }
}