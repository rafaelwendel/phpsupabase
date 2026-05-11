<?php

namespace PHPSupabase;

class AuthAdmin {
    private $suffix = 'auth/v1/admin/';
    private $service;
    private $data;

    /**
     * Construct method (Set the Service instance)
     *
     * The Service instance must be created with the project service_role key
     * (not the anon/public key), since the /auth/v1/admin/* endpoints require
     * elevated privileges. The service_role is sent in both the apikey header
     * (already handled by Service) and the Authorization Bearer header
     * (set here at construction time).
     *
     * @access public
     * @param Service $service The Supabase Service instance, built with the service_role key
     * @return void
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->service->setHeader('Authorization', 'Bearer ' . $service->getApiKey());
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
     * @return string|null
     */
    public function getError() : string|null
    {
        return $this->service->getError();
    }

    /**
     * Build the request options array, optionally with a JSON body
     * @access private
     * @param array $fields Optional. Body fields to be JSON-encoded
     * @return array
     */
    private function buildOptions(array $fields = []) : array
    {
        $options = ['headers' => $this->service->getHeaders()];
        if(count($fields) > 0){
            $options['body'] = json_encode($fields);
        }
        return $options;
    }

    /**
     * Create a new user via the admin endpoint (bypasses disable_signup)
     * @access public
     * @param string $email The email address of the new user
     * @param string $password The password of the new user
     * @param bool $emailConfirm Optional. If true, marks the email as confirmed (default: true)
     * @param array $userMetadata Optional. The user metadata
     * @return void
     */
    public function createUser(string $email, string $password, bool $emailConfirm = true, array $userMetadata = []) : void
    {
        $fields = [
            'email' => $email,
            'password' => $password,
            'email_confirm' => $emailConfirm
        ];
        if(is_array($userMetadata) && count($userMetadata) > 0){
            $fields['user_metadata'] = $userMetadata;
        }

        $uri = $this->service->getUriBase($this->suffix . 'users');
        $this->data = $this->service->executeHttpRequest('POST', $uri, $this->buildOptions($fields));
    }

    /**
     * List users with pagination
     * @access public
     * @param int $page Optional. Page number (default: 1)
     * @param int $perPage Optional. Items per page (default: 50)
     * @return void
     */
    public function listUsers(int $page = 1, int $perPage = 50) : void
    {
        $query = http_build_query(['page' => $page, 'per_page' => $perPage]);
        $uri = $this->service->getUriBase($this->suffix . 'users') . '?' . $query;
        $this->data = $this->service->executeHttpRequest('GET', $uri, $this->buildOptions());
    }

    /**
     * Get a user by id
     * @access public
     * @param string $userId The user id
     * @return void
     */
    public function getUser(string $userId) : void
    {
        $uri = $this->service->getUriBase($this->suffix . 'users/' . $userId);
        $this->data = $this->service->executeHttpRequest('GET', $uri, $this->buildOptions());
    }

    /**
     * Update a user (email, password, user_metadata, app_metadata, ban_duration, ...)
     * @access public
     * @param string $userId The user id
     * @param array $attributes The attributes to update (ex: ['email' => '...', 'password' => '...', 'user_metadata' => [...], 'ban_duration' => '24h'])
     * @return void
     */
    public function updateUser(string $userId, array $attributes) : void
    {
        $uri = $this->service->getUriBase($this->suffix . 'users/' . $userId);
        $this->data = $this->service->executeHttpRequest('PUT', $uri, $this->buildOptions($attributes));
    }

    /**
     * Delete a user by id
     * @access public
     * @param string $userId The user id
     * @return void
     */
    public function deleteUser(string $userId) : void
    {
        $uri = $this->service->getUriBase($this->suffix . 'users/' . $userId);
        $this->data = $this->service->executeHttpRequest('DELETE', $uri, $this->buildOptions());
    }

    /**
     * Generate a link (signup, magiclink, recovery, invite, email_change_current, email_change_new)
     * @access public
     * @param string $type The link type (signup, magiclink, recovery, invite, email_change_current, email_change_new)
     * @param string $email The user email
     * @param array $options Optional. Additional options (ex: ['password' => '...', 'data' => [...], 'redirect_to' => '...'])
     * @return void
     */
    public function generateLink(string $type, string $email, array $options = []) : void
    {
        $fields = array_merge(['type' => $type, 'email' => $email], $options);
        $uri = $this->service->getUriBase($this->suffix . 'generate_link');
        $this->data = $this->service->executeHttpRequest('POST', $uri, $this->buildOptions($fields));
    }
}
