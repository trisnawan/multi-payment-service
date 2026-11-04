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
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach($_SERVER as $name => $value){
            if(strpos($name, 'HTTP_') === 0){
                $headers[str_replace('_', '-', substr($name, 5))] = $value;
            }
        }
        return $this->verifyPayload((string) file_get_contents('php://input'), $headers);
    }

    /** Verifikasi raw body dan header dari framework tanpa mengubah pemakaian verifyData(). */
    public function verifyPayload(string $jsonBody, array $headers): ItemTransaction {
        $this->responseCode = 500;
        $token = getenv('payment.webhook');
        if(!is_string($token) || $token === ''){
            throw new \Exception('Payment webhook secret is not set in environment variables.');
        }
        $headers = array_change_key_case($headers, CASE_LOWER);
        if($jsonBody === ''){
            $this->responseCode = 400;
            throw new \Exception("No data received in the request body.");
        }
        
        $stringToSign = strtolower(hash('sha256', $jsonBody)) . ':' . $token;
        $signature = hash_hmac('sha256', $stringToSign, $token);
        $provided = $headers['signature'] ?? null;
        $providedToken = $headers['m-payment-token'] ?? null;
        if(!is_string($provided) || !hash_equals($signature, $provided)
            || ($providedToken !== null && (!is_string($providedToken) || !hash_equals($token, $providedToken)))){
            $this->responseCode = 403;
            throw new \Exception("Signature verification failed. Invalid signature.");
        }

        $data = json_decode($jsonBody, true);
        if(json_last_error() != JSON_ERROR_NONE || !is_array($data)){
            $this->responseCode = 400;
            throw new \Exception("Invalid JSON format in request body: " . json_last_error_msg());
        }
        $this->responseCode = 200;
        return new ItemTransaction($data);
    }
}
