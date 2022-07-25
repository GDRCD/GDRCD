<?php

class Sessi extends BaseClass
{
    protected function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getGender
     * @note Ottieni un genere
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getGender(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM sessi WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllGenders
     * @note Ottieni tutti i generi presenti
     * @return bool|int|mixed|string
     */
    public function getAllGenders(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM sessi WHERE 1", 'result');
    }


    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageGenders
     * @note Controlla se il personaggio può gestire i generi
     * @return bool
     */
    public function permissionManageGenders(): bool
    {
        return Permissions::permission('MANAGE_GENDERS');
    }

    /**** LISTS ***/

    /**
     * @fn listGenders
     * @note Lista dei sessi disponibili
     * @param int $selected
     * @return mixed
     */
    public function listGenders(int $selected = 0)
    {
        $genders = $this->getAllGenders();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $genders);
    }


    /*** AJAX ***/

    /**
     * @fn DatiAbiRequisito
     * @note Estrae i dati di un requisito abilita
     * @param array $post
     * @return array|false[]|void
     */
    public function ajaxGenderData(array $post)
    {
        if ($this->permissionManageGenders()) {
            $id = Filters::int($post['id']);
            return $this->getGender($id);
        }
    }

    /**** GESTIONE ****/


    /**
     * @fn newGender
     * @note Inserisce un nuovo genere
     * @param array $post
     * @return array
     */
    public function newGender(array $post): array
    {

        if ($this->permissionManageGenders()) {

            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::query("INSERT INTO sessi(nome, immagine) 
                            VALUES ('{$nome}','{$img}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso creato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders()
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
     * @fn modGender
     * @note Modifica un genere
     * @param array $post
     * @return array
     */
    public function modGender(array $post): array
    {

        if ($this->permissionManageGenders()) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);


            DB::query("UPDATE sessi SET nome='{$nome}',immagine='{$img}' WHERE id='{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso modificato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders()
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
     * @fn delGender
     * @note Elimina un genere
     * @param array $post
     * @return array
     */
    public function delGender(array $post): array
    {

        if ($this->permissionManageGenders()) {

            $shop = Filters::int($post['id']);


            DB::query("DELETE FROM sessi WHERE id='{$shop}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso eliminato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders()
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