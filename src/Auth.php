<?php

namespace PHPSupabase;

class Auth {
    private $service;
    private $data;
    private $error;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function data() : object
    {
        return $this->data;
    }

    private function formatRequestException(\GuzzleHttp\Exception\RequestException $e) : void
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
            //die(print_r($msg));
        }
    }

    public function getError() : string
    {
        return $this->error;
    }

    private function defaultPostCallEmailAndPassword(string $endPoint, string $email, string $password)
    {
        $uri = $this->service->getUriBase($endPoint);
        $body = [
            'email' => $email,
            'password' => $password
        ];
        try{
            $response = $this->service->getHttpClient()->post(
                $uri,
                [
                    'headers' => $this->service->getHeaders(),
                    'body'    => json_encode($body)
                ]
            );
            $this->data = json_decode($response->getBody());
        } catch(\GuzzleHttp\Exception\RequestException $e){
            $this->formatRequestException($e);
            throw $e;
        } catch(\GuzzleHttp\Exception\ConnectException $e){
            $this->error = $e->getMessage();
            throw $e;
        }
    }

    public function createUserWithEmailAndPassword(string $email, string $password)
    {
        $this->defaultPostCallEmailAndPassword('signup', $email, $password);
    }

    public function signInWithEmailAndPassword(string $email, string $password)
    {
        $this->defaultPostCallEmailAndPassword('token?grant_type=password', $email, $password);
    }
}