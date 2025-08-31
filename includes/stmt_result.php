<?php

class StmtResultData implements ArrayAccess, Countable, Iterator
{
    public $data;
    private $position = 0;

    public function __construct($data)
    {
        if ($data === null) {
            $this->data = [];
        } else {
            $this->data = array_values($data);
        }
        $this->position = 0;
    }

    // ArrayAccess
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    // Countable
    public function count(): int
    {
        return count($this->data);
    }

    // Iterator
    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}

class StmtResult
{
    public $data;
    public $num_rows;
    public $affected_rows;
    public $last_id;

    public function __construct($rows = [], $num_rows = null, $affected_rows = null, $last_id = null)
    {
        $this->data = new StmtResultData($rows);
        $this->num_rows = $num_rows;
        $this->affected_rows = $affected_rows;
        $this->last_id = $last_id;
    }
}
