<?php

namespace Trisnawan\MultiPaymentService;
use Trisnawan\MultiPaymentService\RequestConfig;
use Trisnawan\MultiPaymentService\ItemTransaction;
use Trisnawan\MultiPaymentService\ItemChannel;
use Trisnawan\MultiPaymentService\ItemCategory;
use Trisnawan\MultiPaymentService\ItemInvoice;
use Trisnawan\MultiPaymentService\PaymentBuyer;
use Trisnawan\MultiPaymentService\PaymentCategory;
use Trisnawan\MultiPaymentService\PaymentInvoice;
use Trisnawan\MultiPaymentService\PaymentDirect;

/**
 * Class RequestPayment
 * 
 * Berisi perintah terkait pembayaran dan invoice. Anda dapat import
 * kode ini ke dalam kode Anda.
 */
class RequestPayment extends RequestConfig {

    /**
     * Menampilkan seluruh metode pembayaran yang tersedia beserta biaya admin dinamis
     * tergantung nominal transaksi.
     * 
     * @param int $amount Berupa nominal transaksi
     * @return array Berisi list objek dari entitas ItemChannel
     */
    public function findPaymentMethod($amount = 0){
        $data = $this->requestGet("payment/method", ['amount'=>$amount]) ?? [];
        return ItemChannel::fromArrayList($data);
    }

    /**
     * Menampilkan seluruh kategori transaksi yang sudah pernah dibuat.
     * 
     * @return array Berisi list objek dari entitas ItemCategory
     */
    public function findCategories(){
        $data = $this->requestGet("payment/categories") ?? [];
        return ItemCategory::fromArrayList($data);
    }

    /**
     * Membuat pembayaran dengan link, yang memungkinkan sistem membuat satu invoice
     * yang nantinya user dapat memilih metode pembayaran pada link yang terbuat.
     * 
     * @param PaymentInvoice $invoice Berupa data invoice
     * @return ItemInvoice Berisi data invoice beserta link
     */
    public function createLink(PaymentInvoice $invoice): ItemInvoice {
        $data = $this->requestPost("payment/link/create", $invoice);
        return new ItemInvoice($data);
    }

    /**
     * Mengambil semua data pembayaran dengan link secara historikal.
     * 
     * @param int $limit Limit per request
     * @param int $offset Jumlah data per request
     * @param string $search Kata kunci untuk pencarian
     * @return ItemInvoice[] Berisi data invoice beserta link
     */
    public function findLink(int $limit = 10, int $offset = 0, string $search = ''): array {
        $data = $this->requestGet("payment/link", ["limit"=>$limit, "offset"=>$offset, "search"=>$search]);
        if(!$data) return [];
        return ItemInvoice::fromArrayList($data);
    }

    /**
     * Mengambil detail pembayaran dengan link.
     * 
     * @param string $id ID pembayaran dengan link
     * @return ItemInvoice Berisi data invoice beserta link
     */
    public function getLink(string $id): ItemInvoice {
        $data = $this->requestGet("payment/link/detail/$id");
        return new ItemInvoice($data);
    }

    /**
     * Membuat pembayaran direct (langsung) dengan mengirimkan metode pembayaran
     * di awal dan user dapat membayar sesuai detail pembayaran.
     * 
     * @param PaymentDirect $direct Data pembayaran direct
     * @return ItemTransaction Berisi data transaksi direct atau redirect
     */
    public function createDirect(PaymentDirect $direct): ItemTransaction {
        $data = $this->requestPost("payment/direct/create", $direct);
        return new ItemTransaction($data);
    }

    /**
     * Mengambil data list transaksi secara historikal
     * 
     * @param int $limit Limit per request
     * @param int $offset Jumlah data per request
     * @param string $status Status pembayaran (paid,unpaid,failed)
     * @param string $search Kata kunci untuk pencarian
     * @return ItemTransaction[] Berisi data transaksi direct atau redirect
     */
    public function findDirect(int $limit = 10, int $offset = 0, string $status = '', string $search = ''): array {
        $data = $this->requestGet("payment/report", ["limit"=>$limit, "offset"=>$offset, "status"=>$status, "search"=>$search]);
        if(!$data) return [];
        return ItemTransaction::fromArrayList($data['data'] ?? []);
    }

    /**
     * Mengambil detail transaksi
     * 
     * @param string $id ID Transaksi
     * @return ItemTransaction Berisi data transaksi direct atau redirect
     */
    public function getDirect(string $id): ItemTransaction {
        $data = $this->requestGet("payment/direct/detail/$id");
        return new ItemTransaction($data);
    }

    /**
     * Mengambil detail laporan transaksi
     * 
     * @param string $id ID Transaksi
     * @return ItemTransaction Berisi data transaksi direct atau redirect
     */
    public function getReport(string $id){
        if(!$id) {
            throw new \Exception("Transaction ID is required.");
        }

        $data = $this->requestGet("payment/report/detail", ["id" => $id]);
        if($data){
            return new ItemTransaction($data);
        }
    }
}