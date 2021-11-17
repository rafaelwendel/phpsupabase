<?php

namespace PHPSupabase;

use Exception;

class QueryBuilder {
    private $service;
    private $query;

    public function __construct(Service $service)
    {
        $this->service = $service;
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

    public function join(string $join) : QueryBuilder
    {
        $this->query['join'][] = $join;
        return $this;
    }
}