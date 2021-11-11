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

    private function defaultPostCallUserManagement(string $endPoint, array $fields)
    {
        $uri = $this->service->getUriBase($endPoint);
        try{
            $response = $this->service->getHttpClient()->post(
                $uri,
                [
                    'headers' => $this->service->getHeaders(),
                    'body'    => json_encode($fields)
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
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('signup', $fields);
    }

    public function signInWithEmailAndPassword(string $email, string $password)
    {
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('token?grant_type=password', $fields);
    }

    public function signInWithMagicLink(string $email)
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('magiclink', $fields);
    }

    public function createUserWithPhoneAndPassword(string $phone, string $password)
    {
        $fields = [
            'phone' => $phone,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('signup', $fields);
    }

    public function signInWithSMSOTP(string $phone)
    {
        $fields = [
            'phone' => $phone
        ];
        $this->defaultPostCallUserManagement('otp', $fields);
    }

    public function recoverPassword(string $email)
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('recover', $fields);
    }
}