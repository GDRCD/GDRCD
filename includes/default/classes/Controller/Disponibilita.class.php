<?php

class Disponibilita extends BaseClass
{

    /**** TABLE HELPERS ****/

    /**
     * @fn getAvailability
     * @note Ottieni una disponibilità
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAvailability(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM disponibilita WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllAvailabilities
     * @note Ottieni tutte le disponibilità
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllAvailabilities(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM disponibilita WHERE 1", []);
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

    /**
     * @fn listAvailabilities
     * @note Ottieni una lista di disponibilità
     * @return string
     * @throws Throwable
     */
    public function listAvailabilities(): string
    {
        $availabilities = $this->getAllAvailabilities();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $availabilities);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxAvailabilityData
     * @note Estrae i dati di una disponibilità
     * @param array $post
     * @return DBQueryInterface|void
     * @throws Throwable
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
     * @throws Throwable
     */
    public function newAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::queryStmt("INSERT INTO disponibilita (nome, immagine) VALUES (:nome, :img)", [
                'nome' => $nome,
                'img' => $img,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilità creata correttamente.',
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
     * @throws Throwable
     */
    public function modAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::queryStmt("UPDATE disponibilita SET nome=:nome, immagine=:img WHERE id=:id", [
                'id' => $id,
                'nome' => $nome,
                'img' => $img,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilità modificata correttamente.',
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
     * @throws Throwable
     */
    public function delAvailability(array $post): array
    {

        if ( $this->permissionManageAvailabilities() ) {

            $shop = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM disponibilita WHERE id=:id", [
                'id' => $shop,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Disponibilità eliminata correttamente.',
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