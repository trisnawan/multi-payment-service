<?php

namespace Trisnawan\MultiPaymentService;

/**
 * Class PaymentCategory
 * 
 * Request data Transaction Category.
 */
class PaymentCategory {
    public $id, $reference_id, $title, $description;

    public function __construct(string $reference_id, string $title, string $description) {
        $this->id = null;
        $this->reference_id = $reference_id;
        $this->title = $title;
        $this->description = $description;
    }
}