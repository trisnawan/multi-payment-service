<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\PaymentRequest;
use Trisnawan\MultiPaymentService\ItemTransaction;
use Trisnawan\MultiPaymentService\ItemChannel;
use Trisnawan\MultiPaymentService\ItemCategory;

class PaymentCategory {
    public $id, $title, $description;

    public function __construct(string $id, string $title, string $description) {
        $this->id = $id; // ID Referensi Kategori
        $this->title = $title; // Judul Kategori
        $this->description = $description; // Deskripsi Kategori
    }
}

class PaymentBuyer {
    public $full_name, $email, $phone;

    public function __construct(string $full_name, string $email, string $phone) {
        $this->full_name = $full_name; // Nama Pelanggan
        $this->email = $email; // Email Pelanggan
        $this->phone = $phone; // Telepon Pelanggan
    }
}

class PaymentInvoice {
    public $title, $description, $amount, $redirect;

    public function __construct(string $title, string $description, int $amount, string|null $redirect = null) {
        $this->title = $title; // Judul Transaksi
        $this->description = $description; // Deskripsi Transaksi
        $this->amount = $amount; // Nominal Transaksi
        $this->redirect = $redirect; // URL Redirecting
    }
}

class PaymentGateway extends PaymentRequest {

    public function findPaymentMethod(){
        $data = $this->requestGet("payment/method") ?? [];
        $channels = [];
        if($data && is_array($data)){
            foreach ($data as $item) {
                $channel = new ItemChannel($item);
                $channels[] = $channel;
            }
        }
        return $channels;
    }

    public function findCategories(){
        $data = $this->requestGet("payment/categories") ?? [];
        $categories = [];
        if($data && is_array($data)){
            foreach ($data as $item) {
                $category = new ItemCategory($item);
                $categories[] = $category;
            }
        }
        return $categories;
    }

    public function createTransaction(string $method_id, string $client_code, PaymentInvoice $invoice, PaymentBuyer $buyer, PaymentCategory $category){
        $data = $this->requestPost("payment/direct/create", [
            "method_id" => $method_id, // ID Metode Pembayaran
            "client_code" => $client_code, // ID Transaksi Sistem
            "title" => $invoice->title,
            "description" => $invoice->description,
            "charge_amount" => $invoice->amount,
            "redirect_url" => $invoice->redirect,
            "buyer_name" => $buyer->full_name,
            "buyer_email" => $buyer->email,
            "buyer_phone" => $buyer->phone,
            "category" => [
                "reference_id" => $category->id,
                "title" => $category->title,
                "description" => $category->description
            ]
        ]);

        if($data){
            return new ItemTransaction($data);
        }
    }

    public function getTransaction(string $id){
        if(!$id) {
            throw new \Exception("Transaction ID is required.");
        }

        $data = $this->requestGet("payment/report/detail", ["id" => $id]);
        if($data){
            return new ItemTransaction($data);
        }
    }
}