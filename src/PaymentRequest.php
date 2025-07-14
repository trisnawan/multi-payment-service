<?php

namespace Trisnawan\MultiPaymentService;
use GuzzleHttp\Client;

class PaymentRequest {
    protected Client $client;
    private int|null $responseCode = null;
    private bool $success = false;
    private array|null $data = null;

    public function __construct(){
        $this->client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'http_errors' => false,
        ]);
    }

    public function isSuccess(){
        return $this->success;
    }

    public function getResponseCode(){
        return $this->responseCode;
    }

    public function getData(){
        return $this->data;
    }

    public function responseRequets($response, $endpoint){
        $this->responseCode = $response->getStatusCode();
        $this->data = json_decode($response->getBody(), true);
        if($response->getStatusCode() >= 200 && $response->getStatusCode() < 300){
            $this->success = true;
            return $this->data;
        }

        throw new \Exception($body['messages']['error'] ?? ('Failed, error ' . $response->getStatusCode()));
    }

    public function requestPost($endpoint, $data){
        $baseApi = getenv('payment.api');
        $token = getenv('payment.token');

        if(!$baseApi || !$token){
            throw new \Exception("Payment API base URL or token is not set in environment variables.");
        }

        $response = $this->client->request('POST', $baseApi.$endpoint, [
            'connect_timeout' => 10,
            'http_errors' => false,
            'json' => $data,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return $this->responseRequets($response, $endpoint);
    }

    public function requestGet($endpoint, $params = null){
        $baseApi = getenv('payment.api');
        $token = getenv('payment.token');

        if(!$baseApi || !$token){
            throw new \Exception("Payment API base URL or token is not set in environment variables.");
        }

        if($params ?? false){
            $endpoint .= "?" . http_build_query($params);
        }
        $response = $this->client->request('GET', $baseApi.$endpoint, [
            'connect_timeout' => 5,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        
        return $this->responseRequets($response, $endpoint);
    }
}