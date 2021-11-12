<?php

namespace PHPSupabase;

class Database {
    private $service;
    private $tableName;
    private $primaryKey;
    private $bearerToken;
    private $result;

    public function __construct(Service $service, string $tableName, $primaryKey)
    {
        $this->service = $service;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getFirstResult()
    {
        return count($this->result) > 0
            ? $this->result[0]
            : [];
    }

    private function executeQuery(string $queryString)
    {
        $uri = $this->service->getUriBase($this->tableName . '?' . $queryString);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        $this->result = $this->service->executeHttpRequest('GET', $uri, $options);
    }

    public function executeDml(string $method, array $data, string $queryString = null)
    {
        $endPoint = ($queryString == null) ? $this->tableName : $this->tableName . '?' . $queryString; 
        $uri = $this->service->getUriBase($endPoint);
        
        $this->service->setHeader('Prefer', 'return=representation');
        $options = [
            'headers' => $this->service->getHeaders(),
            'body' => json_encode($data)
        ];
        return $this->service->executeHttpRequest($method, $uri, $options);
    }

    public function insert(array $data)
    {
        return $this->executeDml('POST', $data);
    }

    public function update(string $id, array $data)
    {
        return $this->executeDml('PATCH', $data, $this->primaryKey . '=eq.' . $id);
    }

    public function fetchAll()
    {
        $this->executeQuery('select=*');
        return $this;
    }

    public function findBy(string $column, string $value)
    {
        $this->executeQuery($column . '=eq.' . $value);
        return $this;
    }

    public function join(string $foreignTable, string $foreignKey)
    {
        $this->executeQuery('select=*,' . $foreignTable . '(' . $foreignKey . ', *)');
        return $this;
    }
}