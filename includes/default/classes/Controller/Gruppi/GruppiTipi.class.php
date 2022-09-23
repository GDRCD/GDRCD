<?php

/**
 * @class GruppiTipi
 * @note Classe che gestisce i tipi di gruppo
 */
class GruppiTipi extends Gruppi
{

    /**
     * @fn __construct
     * @note Costruttore della classe
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**** PERMESSI ****/

    /**
     * @fn permissionManageTypes
     * @note Controlla permessi sulla gestione dei tipi
     * @return bool
     */
    public function permissionManageTypes(): bool
    {
        return Permissions::permission('MANAGE_GROUPS');
    }

    /** TABLE HELPERS */

    /**
     * @fn getType
     * @note Estrae un tipo preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getType(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_tipo WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllTypes
     * @note Estrae tutti i tipi
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllTypes(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_tipo WHERE 1", []);
    }

    /**** LISTE ****/

    /**
     * @fn listTypes
     * @note Genera gli option per i tipi di gruppo
     * @return string
     * @throws Throwable
     */
    public function listTypes(): string
    {
        $types = $this->getAllTypes();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $types);
    }

    /**** AJAX ****/

    /**
     * @fn ajaxTypeData
     * @note Estrae i dati di un tipo dinamicamente
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxTypeData(array $post): DBQueryInterface
    {
        $id = Filters::int($post['id']);
        return $this->getType($id);
    }

    /**** GESTIONE ****/

    /**
     * @fn NewType
     * @note Inserisce un tipo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewType(array $post): array
    {
        if ( $this->permissionManageTypes() ) {

            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);

            DB::queryStmt(
                "INSERT INTO gruppi_tipo (nome,descrizione) VALUES (:nome,:descr)",
                ['nome' => $nome, 'descr' => $descr]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo creato.',
                'swal_type' => 'success',
                'types_list' => $this->listTypes(),
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
     * @fn ModRole
     * @note Aggiorna un ruolo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModType(array $post): array
    {
        if ( $this->permissionManageTypes() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);

            DB::queryStmt(
                "UPDATE gruppi_tipo SET nome=:nome,descrizione=:descr WHERE id=:id",
                ['id' => $id, 'nome' => $nome, 'descr' => $descr]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo modificato.',
                'swal_type' => 'success',
                'types_list' => $this->listTypes(),
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
     * @fn DelRole
     * @note Cancella un ruolo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelType(array $post): array
    {
        if ( $this->permissionManageTypes() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt(
                "DELETE FROM gruppi_tipo WHERE id=:id",
                ['id' => $id]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo eliminato.',
                'swal_type' => 'success',
                'types_list' => $this->listTypes(),
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