<?php

namespace PHPSupabase;

class Table {
    private $service;
    private $tableName;
    private $bearerToken;

    public function __construct(Service $service, string $tableName)
    {
        $this->service = $service;
        $this->tableName = $tableName;
    }
}