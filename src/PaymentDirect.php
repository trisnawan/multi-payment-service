<?php
namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;

/**
 * Class PaymentDirect
 * 
 * Request data Direct Payment.
 * Ini adalah entitas utama untuk membuat transaksi dengan memilih metode pembayaran
 * secara direct (langsung).
 */
class PaymentDirect {
    public string|null $id, $client_code, $title, $description, $redirect_url, $method_id;
    public int|null $charge_amount;
    public string|null $buyer_id, $buyer_name, $buyer_email, $buyer_phone;
    public PaymentBuyer $buyer;
    public PaymentCategory $category;

    public function __construct(
        PaymentBuyer $buyer, PaymentCategory $category, string $method_id, string $reference_id,
        string $title, string $description, int $charge_amount, string $redirect_url
    ) {
        $this->id = null;
        $this->method_id = $method_id;
        $this->client_code = $reference_id;
        $this->title = $title;
        $this->description = $description;
        $this->redirect_url = $redirect_url;
        $this->charge_amount = $charge_amount;
        $this->buyer_id = $buyer->reference_id;
        $this->buyer_name = $buyer->full_name;
        $this->buyer_email = $buyer->email;
        $this->buyer_phone = $buyer->phone;
        $this->category = $category;
    }
}