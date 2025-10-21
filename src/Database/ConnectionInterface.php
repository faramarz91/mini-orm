<?php
namespace MiniORM\Database;

use PDO;

interface ConnectionInterface {
    public function getPdo(): PDO;
    public function getDriverName(): string;
}
