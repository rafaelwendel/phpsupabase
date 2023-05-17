<?php

namespace PHPSupabase;

class Auth {
    private $suffix = 'auth/v1/';
    private $service;
    private $data;

    /**
     * Construct method (Set the Service instance)
     * @access public
     * @param $service Service The Supabase Service instance
     * @return void
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Returns the response data produced by a requisition
     * @access public
     * @return object
     */
    public function data() : object
    {
        return $this->data;
    }

    /**
     * Returns the error generated
     * @access public
     * @return string
     */
    public function getError() : string
    {
        return $this->service->getError();
    }

    /**
     * Default method to call POST requests to users management
     * @access private
     * @param $endPoint String The endpoint of request
     * @param $fields array The body fields to be use in request (Ex: email, password, ...)
     * @return void
     */
    private function defaultPostCallUserManagement(string $endPoint, array $fields) : void
    {
        $uri = $this->service->getUriBase($this->suffix . $endPoint);
        $options = [
            'headers' => $this->service->getHeaders(),
            'body'    => json_encode($fields)
        ];
        $this->data = $this->service->executeHttpRequest('POST', $uri, $options);
    }

    /**
     * Create a new user (by email and password) in Supabase project 
     * @access public
     * @param $email String The email address of new user
     * @param $password String The password of new user
     * @param $data array (optional) The user meta data
     * @return void
     */
    public function createUserWithEmailAndPassword(string $email, string $password, array $data = []) : void
    {
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        if(is_array($data) && count($data) > 0){
            $fields['data'] = $data;
        }

        $this->defaultPostCallUserManagement('signup', $fields);
    }

    /**
     * Sign in (authenticate) in Supabase project (by email and password)
     * @access public
     * @param $email String The user email
     * @param $password String The user password
     * @return void
     */
    public function signInWithEmailAndPassword(string $email, string $password) : void
    {
        $fields = [
            'email' => $email,
            'password' => $password
        ];
        $this->defaultPostCallUserManagement('token?grant_type=password', $fields);
    }

    /**
     * Sign in (authenticate) in Supabase project (by refresh token)
     * @access public
     * @param $refreshToken String The refresh token
     * @return void
     */
    public function signInWithRefreshToken(string $refreshToken) : void
    {
        $fields = [
            'refresh_token' => $refreshToken
        ];
        $this->defaultPostCallUserManagement('token?grant_type=refresh_token', $fields);
    }

    /**
     * Sign in (authenticate) in Supabase project (by magic link sended to user email)
     * @access public
     * @param $email String The user email
     * @return void
     */
    public function signInWithMagicLink(string $email) : void
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('magiclink', $fields);
    }

    /**
     * Create a new user (by phone and password) in Supabase project 
     * @access public
     * @param $phone String The phone number of new user
     * @param $password String The password of new user
     * @param $data array (optional) The user meta data
     * @return void
     */
    public function createUserWithPhoneAndPassword(string $phone, string $password, array $data = []) : void
    {
        $fields = [
            'phone' => $phone,
            'password' => $password
        ];
        if(is_array($data) && count($data) > 0){
            $fields['data'] = $data;
        }

        $this->defaultPostCallUserManagement('signup', $fields);
    }

    /**
     * Sign in (authenticate) in Supabase project (by SMS OTP)
     * @access public
     * @param $phone String The user phone number
     * @return void
     */
    public function signInWithSMSOTP(string $phone) : void
    {
        $fields = [
            'phone' => $phone
        ];
        $this->defaultPostCallUserManagement('otp', $fields);
    }

    /**
     * Recover the user password (by a link sended to user email)
     * @access public
     * @param $email String The user email
     * @return void
     */
    public function recoverPassword(string $email) : void
    {
        $fields = [
            'email' => $email
        ];
        $this->defaultPostCallUserManagement('recover', $fields);
    }

    /**
     * Logout
     * @access public
     * @param $bearerUserToken String The bearer user token (generated in sign in process)
     * @return array|object|null
     */
    public function logout(string $bearerUserToken)
    {
        $uri = $this->service->getUriBase($this->suffix . 'logout');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        return $this->service->executeHttpRequest('POST', $uri, $options);
    }

    /**
     * Returns the user data
     * @access public
     * @param $bearerUserToken String The bearer user token (generated in sign in process)
     * @return array|object|null
     */
    public function getUser(string $bearerUserToken)
    {
        $uri = $this->service->getUriBase($this->suffix . 'user');
        $this->service->setHeader('Authorization', 'Bearer ' . $bearerUserToken);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        return $this->service->executeHttpRequest('GET', $uri, $options);
    }

    /**
     * Verify if the user is authenticated
     * @access public
     * @param $bearerUserToken String The bearer user token (generated in sign in process)
     * @return bool
     */
    public function isAuthenticated(string $bearerUserToken) : bool
    {
        $data = $this->getUser($bearerUserToken);
        return $data->aud == 'authenticated'
            ? true
            : false;
    }

    /**
     * Update the user data
     * @access public
     * @param $bearerUserToken String The bearer user token (generated in sign in process)
     * @param $email String (optional) The user email
     * @param $password String (optional) The user password
     * @param $data array (optional) The user meta data
     * @return array|object|null
     */
    public function updateUser(string $bearerUserToken, string $email = null, string $password = null, array $data = [])
    {
        $uri = $this->service->getUriBase($this->suffix . 'user');
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