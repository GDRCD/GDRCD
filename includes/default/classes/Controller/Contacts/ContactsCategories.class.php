<?php

/**
 * @class ContactsCategories
 * @note Classe per la gestione centralizzata delle categorie dei contatti
 * @required PHP 7.1+
 */
class ContactsCategories extends Contacts
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
     * @fn getCategories
     * @note Estrae una categoria precisa
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getCategories(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM contatti_categorie WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllCategories
     * @note Estrae tutte le categorie
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCategories(string $val = '*'){
        return DB::query("SELECT {$val} FROM contatti_categorie WHERE 1",'result');
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
    /** GESTIONE */

    /**
     * @fn NewCategories
     * @note Inserisce un gruppo
     * @param array $post
     * @return array
     */
    public function NewCategories(array $post): array
    {
        if ($this->permissionManageCategories()) {

            $nome = Filters::in($post['nome']);
            $creato_il=date("Y-m-d H:i:s");
            $creato_da="";


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
     * @fn ModCategories
     * @note Aggiorna un gruppo
     * @param array $post
     * @return array
     */
    public function ModCategories(array $post): array
    {
        if ($this->permissionManageCategories()) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);



            DB::query("UPDATE  contatti_categorie 
                SET nome = '{$nome}' WHERE id='{$id}'");

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
     * @fn DelCategories
     * @note Cancella un gruppo
     * @param array $post
     * @return array
     */
    public function DelCategories(array $post): array
    {
        if ($this->permissionManageCategories()) {

            $id = Filters::in($post['id']);

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