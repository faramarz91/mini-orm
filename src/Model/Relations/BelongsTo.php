<?php
namespace MiniORM\Model\Relations;

use MiniORM\Database\DatabaseManager;
use MiniORM\Query\QueryBuilder;

class BelongsTo extends Relation {
    protected string $related;
    protected string $foreignKey;
    protected string $ownerKey;

    public function __construct($parent, string $related, string $foreignKey = null, string $ownerKey = 'id') {
        parent::__construct($parent);
        $this->related = $related;
        $this->foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';
        $this->ownerKey = $ownerKey;
    }

    public function get() {
        $fk = $this->foreignKey;
        $value = $this->parent->{$fk} ?? null;
        if ($value === null) return null;

        $instance = new $this->related;
        $conn = DatabaseManager::connection();
        $qb = new QueryBuilder($conn, $instance->getTable());
        $qb->setModel($this->related);
        return $qb->where($this->ownerKey, '=', $value)->first();
    }
}
