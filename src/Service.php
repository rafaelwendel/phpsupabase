<?php

namespace PHPSupabase;

use GuzzleHttp\Psr7\Response;

class Service
{
    private $apiKey;
    private $uriBase;
    private $httpClient;
    private $error;
    private $response;

    private $headers = [
        'Content-Type' => 'application/json'
    ];

    /**
     * Construct method (Set the API key, URI base and instance GuzzleHttp client)
     * @access public
     * @param string $apiKey The Supabase project API Key
     * @param string $uriBase API URI base (Ex: "https://abcdefgh.supabase.co/rest/v1/" OR "https://abcdefgh.supabase.co/auth/v1/") 
     * @return void
     */
    public function __construct(string $apiKey, string $uriBase)
    {
        $this->apiKey = $apiKey;
        $this->uriBase = $this->formatUriBase($uriBase);

        $this->httpClient = new \GuzzleHttp\Client();
        $this->headers['apikey'] = $this->apiKey;
    }

    /**
     * Set bearerToken to be added into headers and to be used for future requests
     * @access public
     * @param string $bearerToken The bearer user token (generated in sign in process)  
     * @return Service
     */
    public function setBearerToken($bearerToken)
    {
        $this->setHeader('Authorization', 'Bearer ' . $bearerToken);
        return $this;
    }

    /**
     * Format URI base with slash at end
     * @access private
     * @param string $uriBase API URI base (Ex: "https://abcdefgh.supabase.co/rest/v1/" OR "https://abcdefgh.supabase.co/auth/v1/") 
     * @return string
     */
    private function formatUriBase(string $uriBase): string
    {
        return (substr($uriBase, -1) == '/')
            ? $uriBase
            : $uriBase . '/';
    }

    /**
     * Returns the API key
     * @access public
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Returns the URI base
     * @access public
     * @param string $endPoint Optional. String The end point to concatenate to URI base
     * @return string
     */
    public function getUriBase(string $endPoint = ''): string
    {
        $parseUrl = parse_url($this->uriBase);
        $parseUrl['port'] = isset($parseUrl['port']) ? $parseUrl['port'] : null;
        if ($parseUrl['port'] === null) {
            if (!isset($parseUrl['scheme']) || $parseUrl['scheme'] === 'http') {
                $parseUrl['port'] = 80;
            } elseif ($parseUrl['scheme'] === 'https') {
                $parseUrl['port'] = 443;
            }
        }

        // Prevent error if the uriBase is not a valid url
        if(!isset($parseUrl['scheme']) || !isset($parseUrl['host'])){
            return $this->uriBase . $endPoint;
        }

        return $parseUrl['scheme'] . '://' . $parseUrl['host'] . ':' . $parseUrl['port'] . '/' . $endPoint;
    }

    /**
     * Returns the HTTP Client (GuzzleHttp)
     * @access public
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): \GuzzleHttp\Client
    {
        return $this->httpClient;
    }

    /**
     * Returns the Response of last request
     * @access public
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Set a header to be use in the API request
     * @access public
     * @param string $header The header key to be set
     * @param string $value The value of header
     * @return void
     */
    public function setHeader(string $header, string $value): void
    {
        $this->headers[$header] = $value;
    }

    /**
     * Returns a specific header or null if it doesn't exist
     * @access public
     * @param string $header The header key to be set
     * @return string|null
     */
    public function getHeader(string $header)
    {
        return (isset($this->headers[$header]))
            ? $this->headers[$header]
            : null;
    }

    /**
     * Returns the set headers
     * @access public
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns the error generated
     * @access public
     * @return string|null
     */
    public function getError(): string|null
    {
        return $this->error;
    }

    /**
     * Check if the last response contains an error
     * @access public
     * @return bool
     */
    public function hasError(): bool
    {
        return !is_null($this->error);
    }

    /**
     * Check if the response is a PostgREST error
     * @access public
     * @param mixed $response The response to check
     * @return bool
     */
    public function isPostgrestError($response): bool
    {
        return is_object($response) && isset($response->code) && isset($response->message);
    }

    /**
     * Returns a new instance of Auth class
     * @access public
     * @return Auth
     */
    public function createAuth(): Auth
    {
        return new Auth($this);
    }

    /**
     * Returns a new instance of Database class
     * @access public
     * @param string $tableName The table to be used
     * @param string $primaryKey Optional. String The table primary key (usually "id")
     * @return Database
     */
    public function initializeDatabase(string $tableName, string $primaryKey = 'id'): Database
    {
        return new Database($this, $tableName, $primaryKey);
    }

    /**
     * Returns a new instance of QueryBuilder class
     * @access public
     * @return QueryBuilder
     */
    public function initializeQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this);
    }

    /**
     * Format the exception thrown by GuzzleHttp, formatting the error message
     * @access public
     * @param \GuzzleHttp\Exception\RequestException $e  The exception thrown by GuzzleHttp
     * @return void
     */
    public function formatRequestException(\GuzzleHttp\Exception\RequestException $e): void
    {
        if ($e->hasResponse()) {
            $res = json_decode($e->getResponse()->getBody());
            $searchItems = ['msg', 'message', 'error_description'];

            foreach ($searchItems as $item) {
                if (isset($res->$item)) {
                    $this->error = $res->$item;
                    break;
                }
            }
        }
    }

    /**
     * Execute a Http request in Supabase API
     * @access public
     * @param string $method The request method (GET, POST, PUT, DELETE, PATCH, ...)
     * @param string $uri The URI to be requested (including the endpoint)
     * @param array $options Requisition options (header, body, ...) 
     * @return array|object|null
     */
    public function executeHttpRequest(string $method, string $uri, array $options)
    {
        try {
            $this->response = $this->httpClient->request(
                $method,
                $uri,
                $options
            );
            return json_decode($this->response->getBody());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->formatRequestException($e);
            throw $e;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->error = $e->getMessage();
            throw $e;
        }
    }
}
