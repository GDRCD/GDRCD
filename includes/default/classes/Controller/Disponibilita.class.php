<?php

class Disponibilita extends BaseClass
{
    protected function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getAvailability
     * @note Ottieni una disponibilità
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAvailability(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM disponibilita WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllAvailabilities
     * @note Ottieni tutte le disponibilità
     * @return bool|int|mixed|string
     */
    public function getAllAvailabilities(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM disponibilita WHERE 1", 'result');
    }


    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageAvailabilities
     * @note Controlla se il personaggio può gestire le disponibilità
     * @return bool
     */
    public function permissionManageAvailabilities(): bool
    {
        return Permissions::permission('MANAGE_AVAILABILITIES');
    }

    /**** LISTS ***/

    public function listAvailabilities()
    {
        $availabilities = $this->getAllAvailabilities();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $availabilities);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxAvailabilityData
     * @note Estrae i dati di una disponibilità
     * @param array $post
     * @return array|false[]|void
     */
    public function ajaxAvailabilityData(array $post)
    {
        if ( $this->permissionManageAvailabilities() ) {
            $id = Filters::int($post['id']);
            return $this->getAvailability($id);
        }
    }

    /**** GESTIONE ****/

    /**
     * @fn newAvailability
     * @note Inserisce una nuova disponibilità
     * @param array $post
     * @return array
     */
    public function newAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::query("INSERT INTO disponibilita(nome, immagine) 
                            VALUES ('{$nome}','{$img}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilita creata correttamente.',
                'swal_type' => 'success',
                'availabilities_list' => $this->listAvailabilities(),
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
     * @fn modAvailability
     * @note Modifica una disponibilità
     * @param array $post
     * @return array
     */
    public function modAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::query("UPDATE disponibilita SET nome='{$nome}',immagine='{$img}' WHERE id='{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilita modificata correttamente.',
                'swal_type' => 'success',
                'availabilities_list' => $this->listAvailabilities(),
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
     * @fn delAvailability
     * @note Elimina una disponibilità
     * @param array $post
     * @return array
     */
    public function delAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $shop = Filters::int($post['id']);

            DB::query("DELETE FROM disponibilita WHERE id='{$shop}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilita eliminata correttamente.',
                'swal_type' => 'success',
                'availabilities_list' => $this->listAvailabilities(),
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