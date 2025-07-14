<?php
namespace Trisnawan\MultiPaymentService;

class PaymentInvoice {
    public $title, $description, $amount, $redirect;

    public function __construct(string $title, string $description, int $amount, string|null $redirect = null) {
        $this->title = $title; // Judul Transaksi
        $this->description = $description; // Deskripsi Transaksi
        $this->amount = $amount; // Nominal Transaksi
        $this->redirect = $redirect; // URL Redirecting
    }
}