<?php
namespace MiniORM\Model;

use MiniORM\Database\DatabaseManager;
use MiniORM\Model\Relations\BelongsTo;
use MiniORM\Model\Relations\HasMany;
use MiniORM\Query\QueryBuilder;
use MiniORM\Collections\Collection;
use MiniORM\Exceptions\ModelNotFoundException;
use MiniORM\Exceptions\ORMException;

abstract class Model {
    protected string $table = '';
    protected array $fillable = [];
    protected array $attributes = [];
    protected ?string $connectionName = null;

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function getTable(): string {
        if ($this->table) return $this->table;
        // fallback to snake_plural of class short name
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower($class) . 's';
    }

    public function fill(array $data): void {
        foreach ($data as $k => $v) {
            $this->attributes[$k] = $v;
        }
    }

    public function __get($name) {
        // relation method?
        if (method_exists($this, $name)) {
            $res = $this->{$name}();
            return $res;
        }
        return $this->attributes[$name] ?? null;
    }

    public static function all(): Collection {
        $instance = new static;
        $conn = static::getConnection();
        $qb = new QueryBuilder($conn, $instance->getTable());
        $qb->setModel(static::class);
        return $qb->get();
    }

    public static function get(): Collection {
        // mimic Eloquent-style usage: User::where(...)->get()
        $instance = new static;
        $conn = static::getConnection();
        $qb = new QueryBuilder($conn, $instance->getTable());
        $qb->setModel(static::class);
        return $qb->get();
    }

    public function toArray(): array {
        return $this->attributes;
    }

    public function toJson(): string {
        return json_encode($this->toArray());
    }

    /* ----- CRUD / Query integration ----- */

    protected static function getConnection(): \MiniORM\Database\ConnectionInterface {
        return DatabaseManager::connection();
    }

    public static function query(): QueryBuilder {
        $instance = new static;
        $conn = static::getConnection();
        $qb = new QueryBuilder($conn, $instance->getTable());
        $qb->setModel(static::class);
        return $qb;
    }

    public static function where(string $col, string $op, $val): QueryBuilder {
        return static::query()->where($col, $op, $val);
    }

    public static function create(array $data): static {
        $instance = new static;
        $conn = static::getConnection();
        $pdo = $conn->getPdo();

        $cols = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO `{$instance->getTable()}` (" . implode(',', $cols) . ") VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        $last = $pdo->lastInsertId();
        if ($last) {
            $data['id'] = $last;
        }
        return new static($data);
    }

    public static function find($id): ?static {
        $res = static::query()->where('id', '=', $id)->first();
        if ($res === null) {
            return null;
        }
        // $res may be model instance (QueryBuilder returns model if setModel)
        if ($res instanceof static) return $res;
        return new static((array)$res);
    }

    /**
     * @throws ORMException
     */
    public function update(array $data): bool {
        if (!isset($this->attributes['id'])) {
            throw new ORMException("Model has no id to update");
        }
        $conn = static::getConnection();
        $pdo = $conn->getPdo();
        $sets = [];
        $bindings = [];
        foreach ($data as $k => $v) {
            $sets[] = "`$k` = ?";
            $bindings[] = $v;
        }
        $bindings[] = $this->attributes['id'];
        $sql = "UPDATE `{$this->getTable()}` SET " . implode(',', $sets) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute($bindings);
        if ($ok) {
            foreach ($data as $k => $v) $this->attributes[$k] = $v;
        }
        return $ok;
    }

    /**
     * @throws ORMException
     */
    public function remove(): bool {
        if (!isset($this->attributes['id'])) {
            throw new ORMException("Model has no id to delete");
        }
        return static::deleteById($this->attributes['id']);
    }

    /**
     * @throws ORMException
     */
    public static function delete($id = null): bool {
        if ($id === null) {
            throw new ORMException("ID is required for static delete method");
        }
        return static::deleteById($id);
    }

    private static function deleteById($id): bool {
        $instance = new static;
        $conn = static::getConnection();
        $pdo = $conn->getPdo();
        $stmt = $pdo->prepare("DELETE FROM `{$instance->getTable()}` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /* Relation helpers */
    protected function belongsTo(string $related, string $foreignKey = null, string $ownerKey = 'id'): BelongsTo
    {
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    protected function hasMany(string $related, string $foreignKey = null, string $localKey = 'id'): HasMany
    {
        return new HasMany($this, $related, $foreignKey, $localKey);
    }
}