<?php

class NewsTipo extends News
{

    /**** TABLE HELPERS ****/

    /**
     * @fn getNewsType
     * @note Ottieni una tipologia di news
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getNewsType(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM news_tipo WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllNewsType
     * @note Ottieni tutte le tipologie di news
     * @param bool $active
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllNewsType(bool $active = true, string $val = '*'): DBQueryInterface
    {

        $extra_query = '';

        if ( $active ) {
            $extra_query = ' AND attiva = true ';
        }

        return DB::queryStmt("SELECT {$val} FROM news_tipo WHERE 1 {$extra_query} ORDER BY nome", []);
    }

    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageNewsType
     * @note Controlla se l'utente ha i permessi per gestire le tipologie di news
     * @return bool
     * @throws Throwable
     */
    public function permissionManageNewsType(): bool
    {
        return Permissions::permission('MANAGE_NEWS_TYPE');
    }

    /**** CONTROLS ****/

    /**
     * @fn isTypeActive
     * @note Controlla se la tipologia di news è attiva
     * @param int $type_id
     * @return bool
     * @throws Throwable
     */
    public function isTypeActive(int $type_id): bool
    {
        $type_data = $this->getNewsType($type_id, 'attiva');
        return Filters::bool($type_data['attiva']);
    }

    /**** LISTS ***/

    /**
     * @fn listNewsType
     * @note Ottieni una lista di tipologie di news
     * @param bool $active
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listNewsType(bool $active = true, int $selected = 0): string
    {
        $news_types = $this->getAllNewsType($active);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $news_types);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxNewsTypeData
     * @note Estrae i dati di una tipologia di news
     * @param array $post
     * @return DBQueryInterface|void
     * @throws Throwable
     */
    public function ajaxNewsTypeData(array $post)
    {
        if ( $this->permissionManageNewsType() ) {
            $id = Filters::int($post['id']);
            return $this->getNewsType($id)->getData()[0];
        }
    }

    /**** GESTIONE ****/

    /**
     * @fn newNewsType
     * @note Inserisce una nuova tipologia di news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newNewsType(array $post): array
    {

        if ( $this->permissionManageNewsType() ) {

            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $active = Filters::checkbox($post['attiva']);

            DB::queryStmt("INSERT INTO news_tipo (nome, descrizione, attiva, creata_da) VALUES (:name, :description, :active)", [
                'name' => $name,
                'description' => $description,
                'active' => $active,
                'created_by' => $this->me_id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia news creata correttamente.',
                'swal_type' => 'success',
                'news_types_list' => $this->listNewsType(false),
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
     * @fn modNewsType
     * @note Modifica una tipologia di news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modNewsType(array $post): array
    {

        if ( $this->permissionManageNewsType() ) {

            $id = Filters::int($post['id']);
            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $active = Filters::checkbox($post['attiva']);

            DB::queryStmt("UPDATE news_tipo SET nome=:name, descrizione=:description, attiva=:active WHERE id=:id", [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'active' => $active,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia news modificata correttamente.',
                'swal_type' => 'success',
                'news_types_list' => $this->listNewsType(false),
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
     * @fn delNewsType
     * @note Elimina una tipologia di news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delNewsType(array $post): array
    {

        if ( $this->permissionManageNewsType() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM news_tipo WHERE id=:id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia news eliminata correttamente.',
                'swal_type' => 'success',
                'news_types_list' => $this->listNewsType(false),
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