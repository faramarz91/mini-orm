<?php
namespace MiniORM\Database;

use PDO;
use MiniORM\Exceptions\QueryException;

class Connection implements ConnectionInterface {
    protected PDO $pdo;
    protected string $driver;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }

    public function getDriverName(): string {
        return $this->driver;
    }

    public function prepare(string $sql) {
        try {
            return $this->pdo->prepare($sql);
        } catch (\PDOException $e) {
            throw new QueryException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
