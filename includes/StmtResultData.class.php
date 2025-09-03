<?php

/**
 * Classe StmtResultData
 *
 * Una classe contenitore che incapsula un array di dati e fornisce
 * le interfacce ArrayAccess, Countable e Iterator per un accesso
 * e una manipolazione comodi.
 *
 * Caratteristiche:
 * - Permette l'accesso ai dati come un array tramite ArrayAccess.
 * - Supporta il conteggio degli elementi tramite Countable.
 * - Consente l'iterazione sui dati tramite Iterator.
 *
 * Utilizzo:
 * $result = new StmtResultData($data);
 * foreach ($result as $item) { ... }
 * $count = count($result);
 * $value = $result[0];
 *
 */
class StmtResultData implements Iterator, ArrayAccess, Countable
{
    private array $data;
    private int $position = 0;

    public function __construct(array $data)
    {
        $this->data = array_values($data);
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->data[$this->position] ?? null;
    }

    public function key(): int
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

    // Countable
    public function count(): int
    {
        return count($this->data);
    }

    // ArrayAccess methods
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }
    public function offsetGet($offset): mixed
    {
        return $this->data[$offset] ?? null;
    }
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function free(): void
    {
        unset($this->data);
        $this->position = 0;
    }
}
