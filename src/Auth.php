<?php

namespace PHPSupabase;

class Auth {
    private $service;
    private $data;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function data() : object
    {
        return $this->data;
    }

    public function getError() : string
    {
        return $this->service->getError();
    }

    private function defaultPostCallUserManagement(string $endPoint, array $fields) : void
    {
        $uri = $this->service->getUriBase($endPoint);
        $options = [
            'headers' => $this->service->getHeaders(),
            'body'    => json_encode($fields)
        ];
        $this->data = $this->service->executeHttpRequest('POST', $uri, $options);
    }

    public function createUserWithEmailAndPassword(string $email, string $password) : void
    {
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('signup', $fields);
    }

    public function signInWithEmailAndPassword(string $email, string $password) : void
    {
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('token?grant_type=password', $fields);
    }

    public function signInWithRefreshToken(string $refreshToken) : void
    {
        $fields = [
            'refresh_token' => $refreshToken
        ];
        $this->defaultPostCallUserManagement('token?grant_type=refresh_token', $fields);
    }

    public function signInWithMagicLink(string $email) : void
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('magiclink', $fields);
    }

    public function createUserWithPhoneAndPassword(string $phone, string $password) : void
    {
        $fields = [
            'phone' => $phone,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('signup', $fields);
    }

    public function signInWithSMSOTP(string $phone) : void
    {
        $fields = [
            'phone' => $phone
        ];
        $this->defaultPostCallUserManagement('otp', $fields);
    }

    public function recoverPassword(string $email) : void
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('recover', $fields);
    }

    
    public function logout(string $bearerUserToken) : mixed
    {
        $uri = $this->service->getUriBase('logout');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        return $this->service->executeHttpRequest('POST', $uri, $options);
    }

    public function getUser(string $bearerUserToken) : mixed
    {
        $uri = $this->service->getUriBase('user');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        return $this->service->executeHttpRequest('GET', $uri, $options);
    }

    public function isAuthenticated(string $bearerUserToken) : bool
    {
        $data = $this->getUser($bearerUserToken);
        return $data->aud == 'authenticated'
            ? true
            : false;
    }

    public function updateUser(string $bearerUserToken, string $email = null, string $password = null, array $data = []) : mixed
    {
        $uri = $this->service->getUriBase('user');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);

        $fields = [];
        if(!is_null($email)){
            $fields['email'] = $email;
        }
        if(!is_null($password)){
            $fields['password'] = $password;
        }
        if(is_array($data) && count($data) > 0){
            $fields['data'] = $data;
        }

        $options = [
            'headers' => $this->service->getHeaders(),
            'body' => json_encode($fields)
        ];

        return $this->service->executeHttpRequest('PUT', $uri, $options);
    }

}