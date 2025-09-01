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
}


/**
 * Classe StmtResult
 *
 * Questa classe rappresenta il risultato di una query eseguita su un database.
 * Incapsula i dati restituiti dalla query, il numero di righe coinvolte, il numero di righe affette
 * e l'eventuale ultimo ID inserito (ad esempio in caso di INSERT).
 *
 * Proprietà:
 * @property StmtResultData $data          Oggetto che contiene i dati restituiti dalla query.
 * @property int|null       $num_rows      Numero di righe restituite dalla query (per SELECT) o coinvolte (per UPDATE/DELETE).
 * @property int|null       $affected_rows Numero di righe effettivamente modificate dalla query (per UPDATE/DELETE).
 * @property int|null       $last_id       Ultimo ID inserito nel database (per INSERT), se applicabile.
 *
 * Costruttore:
 * @param array      $rows          Array associativo contenente i dati delle righe restituite dalla query.
 * @param int|null   $num_rows      Numero di righe restituite o coinvolte dalla query.
 * @param int|null   $affected_rows Numero di righe modificate dalla query.
 * @param int|null   $last_id       Ultimo ID inserito, se disponibile.
 */
class StmtResult implements Iterator, ArrayAccess
{
    public StmtResultData $data;
    public ?int $num_rows;
    public ?int $affected_rows;
    public ?int $last_id;

    public function __construct($rows, $num_rows = null, $affected_rows = null, $last_id = null)
    {
        $this->data = new StmtResultData($rows ?? []);
        $this->num_rows = $num_rows;
        $this->affected_rows = $affected_rows;
        $this->last_id = $last_id;
    }

    // Iterator methods: delega a $this->data
    public function current(): mixed
    {
        return $this->data->current();
    }

    public function key(): mixed
    {
        return $this->data->key();
    }

    public function next(): void
    {
        $this->data->next();
    }

    public function rewind(): void
    {
        $this->data->rewind();
    }

    public function valid(): bool
    {
        return $this->data->valid();
    }

    // ArrayAccess methods
    public function offsetExists($offset): bool
    {
        // Gestisci le proprietà della classe StmtResult
        if (in_array($offset, ['num_rows', 'affected_rows', 'last_id'])) {
            return isset($this->$offset);
        }
        // Delega alla classe StmtResultData per gli altri offset
        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        // Gestisci le proprietà della classe StmtResult
        if (in_array($offset, ['num_rows', 'affected_rows', 'last_id'])) {
            return $this->$offset;
        }
        // Delega alla classe StmtResultData per gli altri offset
        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        // Se l'offset è una delle proprietà, assegna il valore
        if (in_array($offset, ['num_rows', 'affected_rows', 'last_id'])) {
            $this->$offset = $value;
        } else {
            // Delega alla classe StmtResultData per gli altri offset
            $this->data->offsetSet($offset, $value);
        }
    }

    public function offsetUnset($offset): void
    {
        // Non permettiamo di "dissettare" le proprietà pubbliche
        if (!in_array($offset, ['num_rows', 'affected_rows', 'last_id'])) {
            // Delega alla classe StmtResultData per gli altri offset
            $this->data->offsetUnset($offset);
        }
    }
}
