<?php
namespace MiniORM\Model\Relations;

abstract class Relation {
    protected $parent;
    public function __construct($parent) {
        $this->parent = $parent;
    }
}
