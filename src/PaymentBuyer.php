<?php

namespace Trisnawan\MultiPaymentService;

/**
 * Class PaymentBuyer
 * 
 * Request data Buyer (User).
 */
class PaymentBuyer {
    public string|null $id, $reference_id;
    public string|null $full_name, $email, $phone;

    public function __construct(string|null $reference_id, string|null $full_name, string|null $email, string|null $phone) {
        $this->id = null;
        $this->reference_id = $reference_id;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->phone = $phone;
    }
}