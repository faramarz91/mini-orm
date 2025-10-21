<?php
namespace MiniORM\Query;

use MiniORM\Database\ConnectionInterface;
use MiniORM\Collections\Collection;
use MiniORM\Exceptions\QueryException;
use PDO;

class QueryBuilder {
    protected ConnectionInterface $connection;
    protected string $table;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?string $modelClass = null;

    public function __construct(ConnectionInterface $connection, string $table) {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function setModel(string $modelClass): self {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function where(string $column, string $operator, $value): self {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $col, string $dir = 'asc'): self {
        $this->orders[] = "{$col} {$dir}";
        return $this;
    }

    public function limit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }

    protected function buildSelect(): string {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        if ($this->orders) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . (int)$this->limit;
        }
        return $sql;
    }

    public function get(): Collection {
        $sql = $this->buildSelect();
        $pdo = $this->connection->getPdo();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($this->bindings);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new QueryException($e->getMessage(), (int)$e->getCode(), $e);
        }

        if ($this->modelClass) {
            $items = array_map(fn($r) => new $this->modelClass($r), $rows);
            return new Collection($items);
        }
        return new Collection($rows);
    }

    public function first() {
        $this->limit(1);
        $c = $this->get();
        return $c->first();
    }

    public function count(): int {
        $sql = "SELECT COUNT(*) as c FROM `{$this->table}`";
        if ($this->wheres) $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    public function exists(): bool {
        return $this->count() > 0;
    }
}
