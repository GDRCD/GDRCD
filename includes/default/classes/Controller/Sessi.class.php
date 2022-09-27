<?php

class Sessi extends BaseClass
{
    /**** TABLE HELPERS ****/

    /**
     * @fn getGender
     * @note Ottieni un genere
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getGender(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM sessi WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllGenders
     * @note Ottieni tutti i generi presenti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGenders(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM sessi WHERE 1", []);
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
     * @throws Throwable
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
     * @return array
     * @throws Throwable
     */
    public function ajaxGenderData(array $post): array
    {
        if ( $this->permissionManageGenders() ) {
            $id = Filters::int($post['id']);
            return $this->getGender($id)->getData()[0];
        }

        return [];
    }

    /**** GESTIONE ****/

    /**
     * @fn newGender
     * @note Inserisce un nuovo genere
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newGender(array $post): array
    {
        if ( $this->permissionManageGenders() ) {
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::queryStmt("INSERT INTO sessi (nome, immagine) VALUES (:nome, :img)", [
                'nome' => $nome,
                'img' => $img,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso creato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders(),
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
     * @fn modGender
     * @note Modifica un genere
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modGender(array $post): array
    {
        if ( $this->permissionManageGenders() ) {
            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);

            DB::queryStmt("UPDATE sessi SET nome=:nome, immagine=:img WHERE id=:id", [
                'id' => $id,
                'nome' => $nome,
                'img' => $img,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso modificato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders(),
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
     * @fn delGender
     * @note Elimina un genere
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delGender(array $post): array
    {
        if ( $this->permissionManageGenders() ) {
            $shop = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM sessi WHERE id=:id", [
                'id' => $shop,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sesso eliminato correttamente.',
                'swal_type' => 'success',
                'genders_list' => $this->listGenders(),
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