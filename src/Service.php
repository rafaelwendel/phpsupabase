<?php

namespace PHPSupabase;

class Service {
    private $apiKey;
    private $uriBase;

    public function __construct(string $apiKey, string $uriBase)
    {
        $this->apiKey = $apiKey;
        $this->uriBase = $this->formatUriBase($uriBase);
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