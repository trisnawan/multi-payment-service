<?php
namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;

/**
 * Class PaymentInvoice
 * 
 * Request data Link Payment.
 * Ini adalah entitas utama untuk membuat transaksi dengan link dengan metode pembayaran
 * dipilih oleh user nanti (payment link).
 */
class PaymentInvoice {
    public string|null $id, $reference_id, $title, $description, $redirect_url;
    public int|null $amount;
    public string|null $buyer_id, $buyer_name, $buyer_email, $buyer_phone;
    public PaymentBuyer $buyer;
    public PaymentCategory $category;
    /** Kedaluwarsa opsional, format tanggal backend; null memakai default backend. */
    public ?string $expired_at = null;

    public function __construct(
        PaymentBuyer $buyer, PaymentCategory $category, string $reference_id,
        string $title, string $description, int $amount, string $redirect_url
    ) {
        $this->id = null;
        $this->reference_id = $reference_id;
        $this->title = $title;
        $this->description = $description;
        $this->redirect_url = $redirect_url;
        $this->amount = $amount;
        $this->buyer_id = $buyer->reference_id;
        $this->buyer_name = $buyer->full_name;
        $this->buyer_email = $buyer->email;
        $this->buyer_phone = $buyer->phone;
        $this->buyer = $buyer;
        $this->category = $category;
    }
}
