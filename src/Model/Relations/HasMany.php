<?php
namespace MiniORM\Model\Relations;

use MiniORM\Database\DatabaseManager;
use MiniORM\Query\QueryBuilder;

class HasMany extends Relation {
    protected string $related;
    protected string $foreignKey;
    protected string $localKey;

    public function __construct($parent, string $related, string $foreignKey = null, string $localKey = 'id') {
        parent::__construct($parent);
        $this->related = $related;
        $this->foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($parent))->getShortName()) . '_id';
        $this->localKey = $localKey;
    }

    public function get() {
        $localValue = $this->parent->{$this->localKey} ?? null;
        $instance = new $this->related;
        $conn = DatabaseManager::connection();
        $qb = new QueryBuilder($conn, $instance->getTable());
        $qb->setModel($this->related);
        return $qb->where($this->foreignKey, '=', $localValue)->get();
    }
}
