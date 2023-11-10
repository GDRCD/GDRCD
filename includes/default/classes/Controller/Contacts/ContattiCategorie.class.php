<?php

/**
 * @class ContattiCategorie
 * @note Classe per la gestione centralizzata delle categorie dei contatti
 * @required PHP 8+
 */
class ContattiCategorie extends Contatti
{

    /*** PERMISSIONS */

    /**
     * @fn permissionManageCategories
     * @note Controlla se si hanno i permessi per vedere i contatti o se sono i propri
     * @return bool
     * @throws Throwable
     */
    public function permissionManageCategories(): bool
    {
        return Permissions::permission('MANAGE_CONTACTS_CATEGORIES');
    }

    /** TABLE HELPERS */

    /**
     * @fn getCategory
     * @note Estrae una categoria precisa
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCategory(int $id, string $val = '*'):DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM contatti_categorie WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllCategories
     * @note Estrae tutte le categorie
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllCategories(string $val = '*'):DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM contatti_categorie WHERE 1", []);
    }

    /**** AJAX ****/

    /**
     * @fn ajaxCategoriesData
     * @note Restituisce i dati delle categorie
     * @param $post
     * @return array
     * @throws Throwable
     */
    public function ajaxCategoriesData($post): array
    {
        $id = Filters::int($post['id']);
        return $this->getCategory($id)->getData()[0];
    }

    /**** LISTE ****/

    /**
     * @fn listCategories
     * @note Genera gli option per i tipi di categorie
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listCategories(int $selected = 0): string
    {
        $types = $this->getAllCategories();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $types);
    }

    /**** GESTIONE ****/

    /**
     * @fn NewCategory
     * @note Inserisce una categoria contatto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewCategory(array $post): array
    {
        if ( $this->permissionManageCategories() ) {

            $nome = Filters::in($post['nome']);
            $creato_da = Filters::int($post['creato_da']);

            DB::queryStmt("INSERT INTO contatti_categorie (nome, creato_il, creato_da) VALUES (:nome, NOW(), :creato_da)", [
                'nome' => $nome,
                'creato_da' => $creato_da
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria creata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories(),
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
     * @fn ModCategory
     * @note Aggiorna una categoria contatto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModCategory(array $post): array
    {
        if ( $this->permissionManageCategories() ) {
            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);

            DB::queryStmt("UPDATE contatti_categorie SET nome=:nome WHERE id=:id", [
                'id' => $id,
                'nome' => $nome
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria modificata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories(),
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
     * @fn DelCategory
     * @note Cancella un gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelCategory(array $post): array
    {
        if ( $this->permissionManageCategories() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM contatti_categorie WHERE id=:id", [
                'id' => $id
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria eliminata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories(),
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