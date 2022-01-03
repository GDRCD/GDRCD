<?php

/**
 * @class Abilita
 * @note Classe per la gestione centralizzata delle abilita
 * @required PHP 7.1+
 */
class Abilita extends BaseClass
{

    protected
        $abi_public,
        $abi_level_cap,
        $default_px_lvl,
        $abi_requirement,
        $requisito_abi,
        $requisito_stat,
        $abi_extra;

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
     * @return bool|int|mixed|string
     */
    public function getAbilita(int $id, string $val = '*'){
        return DB::query("SELECT {$val} FROM abilita WHERE id = '{$id}' LIMIT 1");
    }

    /**
     * @fn getAllAbilita
     * @note Estrae la lista abilita, se specificato un pg ci associa anche il grado
     * @param string $pg
     * @return bool|int
     */
    public function getAllAbilita(string $val = 'abilita.*')
    {
        return DB::query("SELECT {$val} FROM abilita WHERE 1 ORDER BY nome", 'result');
    }

    /**** CONTROLS ****/

    /**
     * @fn extraActive
     * @note Controlla se la tabella `abilita_extra` e' attiva
     * @return bool
     */
    public function extraActive(): bool
    {
        return $this->abi_extra;
    }

    /**
     * @fn requirementActive
     * @note Controlla se la tabella `abilita_requisiti` e' attiva
     * @return bool
     */
    public function requirementActive(): bool
    {
        return $this->abi_requirement;
    }

    public function abiLevelCap():int{
        return $this->abi_level_cap;
    }

    /***** LISTS *****/

    /**
     * @fn ListaAbilita
     * @note Ritorna una serie di option per una select contenente la lista abilita'
     * @return string
     */
    public function listAbilita($selected = 0): string
    {
        $html = '';
        $abis = $this->getAllAbilita();

        foreach ($abis as $abi) {
            $nome = Filters::out($abi['nome']);
            $id = Filters::int($abi['id']);
            $sel = ($id == $selected) ? 'selected' : '';
            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
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