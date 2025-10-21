<?php
namespace MiniORM\Collections;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;

class Collection implements Countable, IteratorAggregate, JsonSerializable {

    public function __construct(protected array $items = []) {
    }

    public function all(): array {
        return $this->items;
    }

    public function count(): int {
        return count($this->items);
    }

    public function first() {
        return $this->items[0] ?? null;
    }

    public function map(callable $fn): Collection {
        return new static(array_map($fn, $this->items));
    }

    public function toArray(): array {
        return array_map(fn($i) => method_exists($i, 'toArray') ? $i->toArray() : $i, $this->items);
    }

    public function toJson(): string {
        return json_encode($this->toArray());
    }

    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->items);
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
