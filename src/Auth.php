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

    private function defaultPostCallUserManagement(string $endPoint, array $fields)
    {
        $uri = $this->service->getUriBase($endPoint);
        $options = [
            'headers' => $this->service->getHeaders(),
            'body'    => json_encode($fields)
        ];
        $this->data = $this->service->executeHttpRequest('POST', $uri, $options);
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

    
    public function logout(string $bearerUserToken)
    {
        $uri = $this->service->getUriBase('logout');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        return $this->service->executeHttpRequest('POST', $uri, $options);
    }

    public function getUser(string $bearerUserToken)
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

}