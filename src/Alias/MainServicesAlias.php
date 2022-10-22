<?php
declare(strict_types=1);

namespace App\Alias;

use ArrayAccess;
use Iterator;

class MainServicesAlias implements Iterator, ArrayAccess {

    protected array $mainServices;

    public function __construct(array $mainServices) {
        
        $this->mainServices = $mainServices;
    }

    // ArrayAccess implementation

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->mainServices[] = $value;
        } else {
            $this->mainServices[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool {
        return isset($this->mainServices[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->mainServices[$offset]);
    }

    public function offsetGet($offset): mixed {
        return isset($this->mainServices[$offset]) ? $this->mainServices[$offset] : null;
    }

    // Iterator implementation

    public function current(): mixed {

        return current($this->mainServices);
    }

    public function next(): void {

        next($this->mainServices);
    }

    public function key(): mixed {

        return key($this->mainServices);
    }

    public function valid(): bool {

        return (current($this->mainServices) !== false);
    }

    public function rewind(): void {

        reset($this->mainServices);
    }
}