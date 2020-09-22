<?php

namespace App\Classes;

use \PDO;

class Db extends PDO
{
    private $stmt;

    public function __construct($dsn, $username, $password, $options)
    {
        parent::__construct($dsn, $username, $password, $options);
    }

    public function prep(string $sql)
    {
        $this->stmt = $this->prepare($sql);
        return $this->stmt;
    }

    public function execute(array $params = null)
    {
        return $this->stmt->execute($params);
    }

    public function run(string $sql, array $params = null)
    {
        try {
            $this->prep($sql);
            $success = $this->execute($params);
        } catch (PDOException $e) {
            dd($e->getMessage(), true);
        }
        return $success;
    }

    public function fetch(string $sql, array $params = null, string $model = null)
    {
        try {
            $this->run($sql, $params);
            if ($model) {
                $data = $this->stmt->fetchAll(PDO::FETCH_CLASS, $model);
            } else {
                $data = $this->stmt->fetchAll();
            }
        } catch (PDOException $e) {
            dd($e->getMessage(), true);
        }
        return $data;
    }

    public function pdoStatement()
    {
        return $this->stmt;
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function columnCount()
    {
        return $this->stmt->columnCount();
    }

    public function queryString()
    {
        return $this->stmt->queryString;
    }

    public function getColumnMeta(int $column_index)
    {
        return $this->stmt->getColumnMeta($column_index);
    }

    public function dump()
    {
        $this->stmt->debugDumpParams();
    }
}
