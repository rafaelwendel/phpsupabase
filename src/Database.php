<?php

namespace PHPSupabase;

class Database {
    private $service;
    private $tableName;
    private $primaryKey;
    private $result;

    public function __construct(Service $service, string $tableName, string $primaryKey)
    {
        $this->service = $service;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }

    public function getError() : string
    {
        return $this->service->getError();
    }

    public function getResult() : mixed
    {
        return $this->result;
    }

    public function getFirstResult() : mixed
    {
        return count($this->result) > 0
            ? $this->result[0]
            : [];
    }

    private function executeQuery(string $queryString) : void
    {
        $uri = $this->service->getUriBase($this->tableName . '?' . $queryString);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        $this->result = $this->service->executeHttpRequest('GET', $uri, $options);
    }

    public function executeDml(string $method, array $data, string $queryString = null) : mixed
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

    public function insert(array $data) : mixed
    {
        return $this->executeDml('POST', $data);
    }

    public function update(string $id, array $data) : mixed
    {
        return $this->executeDml('PATCH', $data, $this->primaryKey . '=eq.' . $id);
    }

    public function delete(string $id) : mixed
    {
        return $this->executeDml('DELETE', [], $this->primaryKey . '=eq.' . $id);
    }

    public function fetchAll() : Database
    {
        $this->executeQuery('select=*');
        return $this;
    }

    public function findBy(string $column, string $value) : Database
    {
        $this->executeQuery($column . '=eq.' . $value);
        return $this;
    }

    public function findByLike(string $column, string $value) : Database
    {
        $this->executeQuery($column . '=like.%' . $value . '%');
        return $this;
    }

    public function join(string $foreignTable, string $foreignKey) : Database
    {
        $this->executeQuery('select=*,' . $foreignTable . '(' . $foreignKey . ', *)');
        return $this;
    }
}