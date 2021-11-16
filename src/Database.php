<?php

namespace PHPSupabase;

use Exception;

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

    public function getResult() : array
    {
        return $this->result;
    }

    public function getFirstResult() : mixed
    {
        return count($this->result) > 0
            ? $this->result[0]
            : [];
    }

    private function executeQuery(string $queryString, string $table = null) : void
    {
        $table = is_null($table)
                ? $this->tableName
                : $table;
        $uri = $this->service->getUriBase($table . '?' . $queryString);
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

    public function createCustomQuery(array $args)
    {
        $query = [];
        $query['select'] = isset($args['select'])
                            ? $args['select']
                            : '*';

        $table = isset($args['from'])
                ? $args['from']
                : $this->tableName;

        if(isset($args['join'])){
            if(is_array($args['join']) && count($args['join']) > 0){
                foreach ($args['join'] as $join){
                    if(is_array($join) && isset($join['table']) && isset($join['tableid'])){
                        $query['join'][] = $join['table'] . 
                                '(' . $join['tableid'] . ',' 
                                . (isset($join['select']) ? $join['select'] : '*') . ')'; 
                    }
                    else{
                        throw new Exception('"JOIN" argument must have "table" and "tableid" keys');
                    }
                }
            }
            else {
                throw new Exception('"JOIN" argument must be an array');
            }
        }

        if(isset($args['where'])){
            if(is_array($args['where']) && count($args['where']) > 0){
                foreach ($args['where'] as $key => $where){
                    $query['where'][] = $key . '=' . $where;
                }
            }
            else{
                throw new Exception('"WHERE" argument must be an array');
            }
        }

        $queryString = 'select=' . $query['select'];
        if(isset($query['join'])){
            $queryString .= ',' . implode(',', $query['join']);
        }
        if(isset($query['where'])){
            $queryString .= '&' . implode(',', $query['where']);
        }
        if(isset($args['range'])){
            $this->service->setHeader('Range', $args['range']);
        }
        $this->executeQuery($queryString, $table);
        return $this;
    }
}