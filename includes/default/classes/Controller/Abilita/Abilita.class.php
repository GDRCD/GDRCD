<?php

/**
 * @class Abilita
 * @note Classe per la gestione centralizzata delle abilita
 * @required PHP 7.1+
 */
class Abilita extends BaseClass
{

    protected bool
        $abi_public,
        $abi_requirement,
        $abi_extra;

    protected int
        $requisito_abi,
        $requisito_stat,
        $abi_level_cap,
        $default_px_lvl;

    public function __construct()
    {
        parent::__construct();

        # Livello massimo abilita
        $this->abi_level_cap = Functions::get_constant('ABI_LEVEL_CAP');

        # Default moltiplicatore livello, se costo non specificato in abi_extra
        $this->default_px_lvl = Functions::get_constant('DEFAULT_PX_PER_LVL');

        # I requisiti abilita' sono attivi?
        $this->abi_requirement = Functions::get_constant('ABI_REQUIREMENT');

        # Requisito di tipo abilita
        $this->requisito_abi = Functions::get_constant('REQUISITO_ABI');

        # Requisito di tipo statistica
        $this->requisito_stat = Functions::get_constant('REQUISITO_STAT');

        # I dati abilita' extra sono attivi?
        $this->abi_extra = Functions::get_constant('ABI_EXTRA');
    }

    /*** TABLE HELPER */

    /**
     * @fn getAbilita
     * @note Ottiene i dati di un'abilita'
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAbilita(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM abilita WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllAbilita
     * @note Estrae la lista abilita, se specificato un pg ci associa anche il grado
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllAbilita(string $val = 'abilita.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM abilita WHERE 1 ORDER BY nome", []);
    }

    /**
     * @fn getAllAbilitaByRace
     * @note Estrae la lista abilita da razza
     * @param int $race
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllAbilitaByRace(int $race, string $val = 'abilita.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM abilita WHERE razza=:race ORDER BY nome", ['race' => $race]);
    }

    /**** CONTROLS ****/

    /**
     * @fn extraActive
     * @note Controlla se la tabella `abilita_extra` è attiva
     * @return bool
     */
    public function extraActive(): bool
    {
        return $this->abi_extra;
    }

    /**
     * @fn requirementActive
     * @note Controlla se la tabella `abilita_requisiti` è attiva
     * @return bool
     */
    public function requirementActive(): bool
    {
        return $this->abi_requirement;
    }

    /**
     * @fn abiLevelCap
     * @note Restituisce il livello massimo di un'abilita'
     * @return int
     */
    public function abiLevelCap(): int
    {
        return $this->abi_level_cap;
    }

    /***** LISTS *****/

    /**
     * @fn ListaAbilita
     * @note Ritorna una serie di option per una select contenente la lista abilita'
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listAbilita(int $selected = 0): string
    {
        $abilities = $this->getAllAbilita();
        return Template::getInstance()->renderSelect('id', 'nome', $selected, $abilities);
    }

    /*** FUNCTIONS ***/

    /**
     * @fn defaultCalcUp
     * @note Formula di default in caso di valore assente
     * @param int $grado
     * @return int
     */
    public function defaultCalcUp(int $grado): int
    {
        return round(Filters::int($grado) * $this->default_px_lvl);
    }

}