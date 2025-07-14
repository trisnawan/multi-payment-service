<?php

namespace Trisnawan\MultiPaymentService;

class PaymentBuyer {
    public $full_name, $email, $phone;

    public function __construct(string $full_name, string $email, string $phone) {
        $this->full_name = $full_name; // Nama Pelanggan
        $this->email = $email; // Email Pelanggan
        $this->phone = $phone; // Telepon Pelanggan
    }
}