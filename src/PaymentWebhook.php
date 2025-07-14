<?php

namespace Trisnawan\MultiPaymentService;

class PaymentWebhook {
    public $responseCode = 500;

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