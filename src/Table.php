<?php

namespace PHPSupabase;

class Table {
    private $service;
    private $tableName;
    private $bearerToken;
    private $result;

    public function __construct(Service $service, string $tableName)
    {
        $this->service = $service;
        $this->tableName = $tableName;
    }

    public function getResult()
    {
        return $this->result;
    }

    private function defaultGetCall(string $queryString)
    {
        $uri = $this->service->getUriBase($this->tableName . '?' . $queryString);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        $this->result = $this->service->executeHttpRequest('GET', $uri, $options);
    }

    public function fetchAll()
    {
        $this->defaultGetCall('select=*');
        return $this;
    }
}