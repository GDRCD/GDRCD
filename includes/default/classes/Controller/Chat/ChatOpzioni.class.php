<?php

class ChatOpzioni extends BaseClass
{

    /**
     * @fn __construct
     * @note ChatOpzioni constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /*** TABLES HELPERS ***/

    /**
     * @fn getOption
     * @note Ottiene una specifica opzione chat
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getOption(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM chat_opzioni WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllOptions
     * @note Ottiene tutte le opzioni chat
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllOptions(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM chat_opzioni WHERE 1", []);
    }

    /*** PERMESSI ****/

    /**
     * @fn permissionManageOptions
     * @note Controlla se si hanno i permessi per gestire le opzioni chat
     * @return bool
     */
    public function permissionManageOptions(): bool
    {
        return Permissions::permission('MANAGE_CHAT_OPTIONS');
    }

    /*** LISTS ***/

    /**
     * @fn listTypes
     * @note Genera gli option per il tipo di valore
     * @return string
     */
    public function listTypes(): string
    {
        $types = [
            ["id" => 'string', 'nome' => 'Stringa'],
            ["id" => 'bool', 'nome' => 'Booleano'],
            ["id" => 'int', 'nome' => 'Numero'],
        ];
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $types);
    }

    /**
     * @fn listOptions
     * @note Genera gli option per le opzioni chat
     * @return string
     * @throws Throwable
     */
    public function listOptions(): string
    {
        $options = $this->getAllOptions();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $options);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxOptionData
     * @note Estrae i dati di un'opzione specifica
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxOptionData(array $post): array
    {

        if ( $this->permissionManageOptions() ) {
            $id = Filters::int($post['id']);

            $data = $this->getOption($id);

            $nome = Filters::out($data['nome']);
            $descr = Filters::text($data['descrizione']);
            $tipo = Filters::out($data['tipo']);
            $titolo = Filters::out($data['titolo']);

            return [
                'response' => true,
                'nome' => $nome,
                'titolo' => $titolo,
                'descrizione' => $descr,
                'tipo' => $tipo,
            ];

        }

        return ['response' => false];
    }

    /*** GESTIONE ***/

    /**
     * @fn NewOption
     * @note Crea una nuova opzione chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $tipo = Filters::in($post['tipo']);
            $titolo = Filters::in($post['titolo']);


            DB::queryStmt("INSERT INTO chat_opzioni (nome, descrizione, tipo, titolo,creato_da) VALUES (:nome, :descr, :tipo, :titolo, :creato_da)", [
                'nome' => $nome,
                'descr' => $descr,
                'tipo' => $tipo,
                'titolo' => $titolo,
                "creato_da" => $this->me_id
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Opzione chat creata',
                'swal_type' => 'success',
                "options_list" => $this->listOptions(),
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
     * @fn ModOption
     * @note Aggiorna un'opzione chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $tipo = Filters::in($post['tipo']);
            $titolo = Filters::in($post['titolo']);

            DB::queryStmt("UPDATE chat_opzioni SET nome=:nome, descrizione=:descr, tipo=:tipo, titolo=:titolo WHERE id=:id", [
                'id' => $id,
                'nome' => $nome,
                'descr' => $descr,
                'tipo' => $tipo,
                'titolo' => $titolo,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Opzione chat modificata.',
                'swal_type' => 'success',
                'options_list' => $this->listOptions(),
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
     * @fn DelOption
     * @note Cancella un'opzione chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM chat_opzioni WHERE id=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM personaggio_chat_opzioni WHERE opzione=:id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione meteo eliminata.',
                'swal_type' => 'success',
                'options_list' => $this->listOptions(),
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