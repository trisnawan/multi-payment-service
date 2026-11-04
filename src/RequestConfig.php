<?php

namespace Trisnawan\MultiPaymentService;
use GuzzleHttp\Client;

/**
 * Class RequestConfig
 * 
 * Konfigurasi utama API Request dan Response
 */
class RequestConfig {
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

    /**
     * Memberikan status request ke API.
     * @return bool Status request API true/false
     */
    public function isSuccess(): bool {
        return $this->success;
    }

    /**
     * Memberika kode respon HTTP.
     * @return int|null HTTP Response Code
     */
    public function getResponseCode(): int|null {
        return $this->responseCode;
    }

    /**
     * Memberikan data response dari API.
     * @return array|null Array raw data dari API
     */
    public function getData(): array|null {
        return $this->data;
    }

    public function responseRequets($response, $endpoint){
        $this->success = false;
        $this->responseCode = $response->getStatusCode();
        $decoded = json_decode((string) $response->getBody(), true);
        $this->data = is_array($decoded) ? $decoded : null;
        if($response->getStatusCode() >= 200 && $response->getStatusCode() < 300){
            if($this->data === null && $this->responseCode !== 204){
                throw new \Exception('Invalid JSON response from Payment API.', $this->responseCode);
            }
            $this->success = true;
            return $this->data;
        }

        $message = $this->data['messages']['error'] ?? $this->data['message'] ?? ('Failed, error ' . $this->responseCode);
        throw new \Exception(is_string($message) ? $message : json_encode($message), $this->responseCode);
    }

    private function resetResponse(): void {
        $this->success = false;
        $this->responseCode = null;
        $this->data = null;
    }

    /**
     * Jalankan request POST dengan fungsi ini.
     * 
     * @param string        $endpoint   Path URL, contoh: payment/direct
     * @param object|array  $data       Data yang akan diteruskan ke API
     * @return array|null               Respon API berupa array raw
     */
    public function requestPost(string $endpoint, object|array $data){
        $this->resetResponse();
        $baseApi = getenv('payment.api');
        $token = getenv('payment.token');

        if(!$baseApi || !$token){
            throw new \Exception("Payment API base URL or token is not set in environment variables.");
        }

        $response = $this->client->request('POST', rtrim($baseApi, '/') . '/' . ltrim($endpoint, '/'), [
            'connect_timeout' => 10,
            'http_errors' => false,
            'body' => json_encode($data),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        return $this->responseRequets($response, $endpoint);
    }

    /**
     * Jalankan request GET dengan fungsi ini.
     * 
     * @param string        $endpoint   Path URL, contoh: payment/direct
     * @param object|array  $params     Parameter yang akan diteruskan ke API
     * @return array|null               Respon API berupa array raw
     */
    public function requestGet(string $endpoint, array|null $params = null){
        $this->resetResponse();
        $baseApi = getenv('payment.api');
        $token = getenv('payment.token');

        if(!$baseApi || !$token){
            throw new \Exception("Payment API base URL or token is not set in environment variables.");
        }

        if($params ?? false){
            $endpoint .= (strpos($endpoint, '?') === false ? '?' : '&') . http_build_query($params);
        }
        $response = $this->client->request('GET', rtrim($baseApi, '/') . '/' . ltrim($endpoint, '/'), [
            'connect_timeout' => 5,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        
        return $this->responseRequets($response, $endpoint);
    }
}
