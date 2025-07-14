<?php

namespace Trisnawan\MultiPaymentService;

class PaymentCategory {
    public $id, $title, $description;

    public function __construct(string $id, string $title, string $description) {
        $this->id = $id; // ID Referensi Kategori
        $this->title = $title; // Judul Kategori
        $this->description = $description; // Deskripsi Kategori
    }
}