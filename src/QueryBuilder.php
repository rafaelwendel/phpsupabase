<?php

namespace PHPSupabase;

use Exception;
use GuzzleHttp\Psr7\Query;

class QueryBuilder {
    private $service;
    private $query;
    private $result;

    public function __construct(Service $service)
    {
        $this->service = $service;
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

    public function select(string $select) : QueryBuilder
    {
        $this->query['select'] = $select;
        return $this;
    }

    public function from(string $from) : QueryBuilder
    {
        $this->query['from'] = $from;
        return $this;
    }

    public function join(string $table, string $tablekey, string $select = null) : QueryBuilder
    {
        $this->query['join'][] = $table . 
                                '(' . $tablekey . ',' .
                                (!is_null($select) ? $select : '*') . ')';
        return $this;
    }

    public function where(string $column, string $value) : QueryBuilder
    {
        $this->query['where'][] = $column . '=' . $value;
        return $this;
    }

    public function range(string $range) : QueryBuilder
    {
        $this->query['range'] = $range;
        return $this;
    }

    public function execute() : QueryBuilder
    {
        $this->query['select'] = isset($this->query['select'])
                                ? $this->query['select']
                                : '*';
        if(!isset($this->query['from'])){
            throw new Exception('The table is not defined');
        }

        $queryString = 'select=' . $this->query['select'];
        
        if(isset($this->query['join'])){
            $queryString .= ',' . implode(',', $this->query['join']);
        }

        if(isset($this->query['where'])){
            $queryString .= '&' . implode('&', $this->query['where']);
        }

        if(isset($this->query['range'])){
            $this->service->setHeader('Range', $this->query['range']);
        }
        
        $this->executeQuery($queryString);
        return $this;
    }

    private function executeQuery(string $queryString) : void
    {
        $uri = $this->service->getUriBase($this->query['from'] . '?' . $queryString);
        $options = [
            'headers' => $this->service->getHeaders()
        ];
        $this->result = $this->service->executeHttpRequest('GET', $uri, $options);
    }
}