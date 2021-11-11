<?php

namespace PHPSupabase;

class PHPSupabase {
    private $httpClient;
    private $apiKey;
    private $uriBase;
    private $headers = [
        'Content-Type' => 'application/json'
    ];

    public function __construct(string $apiKey, string $uriBase)
    {
        $this->apiKey = $apiKey;
        $this->uriBase = $this->formatUriBase($uriBase);
        $this->httpClient = new \GuzzleHttp\Client();

        $this->headers['apikey'] = $this->apiKey;
    }

    private function formatUriBase(string $uriBase)
    {
        return (substr($uriBase , -1) == '/')
            ? $uriBase
            : $uriBase . '/'; 
    }

    public function getApiKey() : string
    {
        return $this->apiKey;
    }

    public function getUriBase($endPoint = '')
    {
        return $this->uriBase . $endPoint;
    }

}