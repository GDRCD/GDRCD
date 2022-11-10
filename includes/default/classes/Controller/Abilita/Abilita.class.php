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

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
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


    /*** PERMISSIONS ***/

    /**
     * @fn permissionManageAbility
     * @note Controlla se l'utente ha i permessi per gestire le abilita
     * @return bool
     * @throws Throwable
     */
    public function permissionManageAbility()
    {
        return Permissions::permission('MANAGE_ABILITY');
    }


    /*** TABLE HELPER */

    /**
     * @fn getAbility
     * @note Ottiene i dati di un'abilita'
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAbility(int $id, string $val = '*'): DBQueryInterface
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
    public function listAbility(int $selected = 0): string
    {
        $abilities = $this->getAllAbilita();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $abilities);
    }


    /***** AJAX *****/

    /**
     * @fn ajaxAbilityData
     * @note Estrae i dati di un'abilita'
     * @param array $post
     * @return DBQueryInterface|void
     * @throws Throwable
     */
    public function ajaxAbilityData(array $post)
    {
        if ( $this->permissionManageAbility() ) {
            $id = Filters::int($post['id']);
            return $this->getAbility($id)->getData()[0];
        }
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


    /**** GESTIONE ****/

    /**
     * @fn newAbility
     * @note Inserisce una nuova abilita'
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newAbility(array $post): array
    {

        if ( $this->permissionManageAbility() ) {

            $name = Filters::in($post['nome']);
            $stat = Filters::in($post['statistica']);
            $desc = Filters::in($post['descrizione']);
            $race = Filters::in($post['razza']);

            DB::queryStmt("INSERT INTO abilita (nome, descrizione,statistica,razza) VALUES (:name, :description,:stat,:race)", [
                'name' => $name,
                'description' => $desc,
                'stat' => $stat,
                'race' => $race,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Abilita creata correttamente.',
                'swal_type' => 'success',
                'ability_list' => $this->listAbility(),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn modAbility
     * @note Modifica i dati di un'abilita'
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modAbility(array $post): array
    {

        if ( $this->permissionManageAbility() ) {

            $id = Filters::int($post['id']);
            $name = Filters::in($post['nome']);
            $stat = Filters::in($post['statistica']);
            $desc = Filters::in($post['descrizione']);
            $race = Filters::in($post['razza']);

            DB::queryStmt("UPDATE abilita SET nome = :name, descrizione = :description,statistica = :stat,razza = :race WHERE id=:id", [
                'id' => $id,
                'name' => $name,
                'description' => $desc,
                'stat' => $stat,
                'race' => $race,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Abilita modificata correttamente.',
                'swal_type' => 'success',
                'ability_list' => $this->listAbility(),
            ];

        } else {

            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn delAbility
     * @note Elimina un'abilita'
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delAbility(array $post): array
    {

        if ( $this->permissionManageAbility() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM abilita_extra WHERE abilita=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM abilita_requisiti WHERE abilita=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM personaggio_abilita WHERE abilita=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM abilita WHERE id=:id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Abilita eliminata correttamente.',
                'swal_type' => 'success',
                'ability_list' => $this->listAbility(),
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

}