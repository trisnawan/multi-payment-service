<?php

namespace Trisnawan\MultiPaymentService;

/**
 * Class Webhook
 * 
 * Untuk memverifikasi data yang diterima dari server payment untuk
 * memberikan detail update data pada transaksi.
 * 
 * Tips:
 * 1. Gunakan 'client_code' untuk verifikasi data sebagai ID Referensi Anda
 * 2. Gunakan 'status' untuk menentukan status pembayaran (paid atau unpaid)
 * 3. Gunakan 'charge_amount' sebagai nominal transaksi sebelum biaya admin
 * 4. Gunakan 'total_amount' sebagai nominal transaksi setelah biaya admin
 * 5. Gunakan 'fee_amount' untuk nominal biaya admin
 */
class Webhook {
    public $responseCode = 500;

    /**
     * Memverifikasi request dari server
     * 
     * @return ItemTransaction Berisi data transaksi direct atau redirect
     */
    public function verifyData(){
        $token = getenv('payment.webhook');
        $headers = getallheaders();
        $jsonBody = file_get_contents("php://input");
        if(!$jsonBody){
            $this->responseCode = 400;
            throw new \Exception("No data received in the request body.");
        }
        
        $stringToSign = strtolower(hash('sha256', $jsonBody)) . ':' . $token;
        $signature = hash_hmac('sha256', $stringToSign, $token);
        if(!in_array($signature, [$headers['signature'] ?? null, $headers['Signature'] ?? null])){
            $this->responseCode = 403;
            throw new \Exception("Signature verification failed. Invalid signature.");
        }

        $data = json_decode($jsonBody, true);
        if(json_last_error() != JSON_ERROR_NONE){
            $this->responseCode = 400;
            throw new \Exception("Invalid JSON format in request body: " . json_last_error_msg());
        }
        return new \Trisnawan\MultiPaymentService\ItemTransaction($data);
    }
}