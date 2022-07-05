<?php

/**
 * @class ContattiCategorie
 * @note Classe per la gestione centralizzata delle categorie dei contatti
 * @required PHP 7.1+
 */
class ContattiCategorie extends Contatti
{

    /*** PERMISSIONS */

    /**
     * @fn permissionManageCategories
     * @note Controlla se si hanno i permessi per vedere i contatti o se sono i propri
     * @return bool
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
     * @return bool|int|mixed|string
     */
    public function getCategory(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM contatti_categorie WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllCategories
     * @note Estrae tutte le categorie
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCategories(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM contatti_categorie WHERE 1", 'result');
    }

    /** LISTE */

    /**
     * @fn listCategories
     * @note Genera gli option per i tipi di categorie
     * @return string
     */
    public function listCategories(): string
    {
        $types = $this->getAllCategories();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $types);
    }

    /**
     * @fn listCategories
     * @note Genera gli option per i tipi di categorie
     * @param int $selected
     * @return string
     */
    public function listCategoriesToUpdate(int $selected): string
    {
        $types = $this->getAllCategories();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $types);
    }

    /** GESTIONE */

    /**
     * @fn NewCategory
     * @note Inserisce una categoria contatto
     * @param array $post
     * @return array
     */
    public function NewCategory(array $post): array
    {
        if ($this->permissionManageCategories()) {

            $nome = Filters::in($post['nome']);
            $creato_il = date("Y-m-d H:i:s");
            $creato_da = Filters::int($post['creato_da']);

            DB::query("INSERT INTO contatti_categorie (nome, creato_il, creato_da )  VALUES ('{$nome}','{$creato_il}','{$creato_da}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria creata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }


    /**
     * @fn ModCategory
     * @note Aggiorna una categoria contatto
     * @param array $post
     * @return array
     */
    public function ModCategory(array $post): array
    {
        if ($this->permissionManageCategories()) {
            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);

            DB::query("UPDATE  contatti_categorie SET nome = '{$nome}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria modificata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn DelCategory
     * @note Cancella un gruppo
     * @param array $post
     * @return array
     */
    public function DelCategory(array $post): array
    {
        if ($this->permissionManageCategories()) {

            $id = Filters::int($post['id']);

            DB::query("DELETE FROM contatti_categorie WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria eliminata.',
                'swal_type' => 'success',
                'categories_list' => $this->listCategories()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }
}