<?php
/**
 * @class GruppiTipi
 * @note Classe che gestisce i tipi di gruppo
 */
class GruppiTipi extends Gruppi
{

    protected function __construct()
    {
        parent::__construct();
    }

    public function getType(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_tipo WHERE id='{$id}' LIMIT 1");
    }

    public function getAllTypes(string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_tipo WHERE 1",'result');
    }

    /**
     * @fn listTypes
     * @note Genera gli option per i tipi di gruppo
     * @return string
     */
    public function listTypes(): string
    {
        $types = $this->getAllTypes();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $types);
    }
}