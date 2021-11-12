<?php

namespace PHPSupabase;

class Service {
    private $apiKey;
    private $uriBase;
    private $httpClient;
    private $error;

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

    private function formatUriBase(string $uriBase) : string
    {
        return (substr($uriBase , -1) == '/')
            ? $uriBase
            : $uriBase . '/'; 
    }

    public function getApiKey() : string
    {
        return $this->apiKey;
    }

    public function getUriBase(string $endPoint = '') : string
    {
        return $this->uriBase . $endPoint;
    }

    public function getHttpClient() : \GuzzleHttp\Client
    {
        return $this->httpClient;
    }

    public function setHeader(string $header, string $value) : void
    {
        $this->header[$header] = $value;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    public function getError() : string
    {
        return $this->error;
    }

    public function createAuth()
    {
        return new Auth($this);
    }

    public function formatRequestException(\GuzzleHttp\Exception\RequestException $e) : void
    {
        if($e->hasResponse()){
            $res = json_decode($e->getResponse()->getBody());
            $seacrhItems = ['msg', 'message', 'error_description'];

            foreach($seacrhItems as $item){
                if(isset($res->$item)){
                    $this->error = $res->$item;
                    break;
                }
            }
        }
    }

    public function executeHttpRequest(string $method, string $uri, array $options)
    {
        try{
            $response = $this->httpClient->request(
                $method,
                $uri,
                $options
            );
            return json_decode($response->getBody());
        } catch(\GuzzleHttp\Exception\RequestException $e){
            $this->formatRequestException($e);
            throw $e;
        } catch(\GuzzleHttp\Exception\ConnectException $e){
            $this->error = $e->getMessage();
            throw $e;
        }
    }
}