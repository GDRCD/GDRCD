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

/**
 * Classe StmtResult
 *
 * Questa classe rappresenta il risultato di una query eseguita su un database.
 * Incapsula i dati restituiti dalla query, il numero di righe coinvolte, il numero di righe affette
 * e l'eventuale ultimo ID inserito (ad esempio in caso di INSERT).
 *
 * Proprietà:
 * @property StmtResultData $data           Oggetto che contiene i dati restituiti dalla query.
 * @property int|null       $num_rows       Numero di righe restituite dalla query (per SELECT) o coinvolte (per UPDATE/DELETE).
 * @property int|null       $affected_rows  Numero di righe effettivamente modificate dalla query (per UPDATE/DELETE).
 * @property int|null       $last_id        Ultimo ID inserito nel database (per INSERT), se applicabile.
 *
 * Costruttore:
 * @param array     $rows           Array associativo contenente i dati delle righe restituite dalla query.
 * @param int|null  $num_rows       Numero di righe restituite o coinvolte dalla query.
 * @param int|null  $affected_rows  Numero di righe modificate dalla query.
 * @param int|null  $last_id        Ultimo ID inserito, se disponibile.
 */
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
