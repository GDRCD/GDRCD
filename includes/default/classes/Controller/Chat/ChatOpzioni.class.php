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
     * @return bool|int|mixed|string
     */
    public function getOption(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM chat_opzioni WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllOptions
     * @note Ottiene tutte le opzioni chat
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllOptions(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM chat_opzioni WHERE 1", 'result');
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
     */
    public function NewOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $tipo = Filters::in($post['tipo']);
            $titolo = Filters::in($post['titolo']);

            DB::query("INSERT INTO chat_opzioni(nome,titolo,descrizione,tipo,creato_da) VALUES('{$nome}','{$titolo}','{$descr}','{$tipo}','{$this->me_id}')");

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
     */
    public function ModOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $tipo = Filters::in($post['tipo']);
            $titolo = Filters::in($post['titolo']);

            DB::query("UPDATE  chat_opzioni 
                SET nome = '{$nome}',titolo='{$titolo}',descrizione='{$descr}', tipo='{$tipo}' WHERE id='{$id}'");

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
     */
    public function DelOption(array $post): array
    {
        if ( $this->permissionManageOptions() ) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM chat_opzioni WHERE id='{$id}'");
            DB::query("DELETE FROM personaggio_chat_opzioni WHERE opzione='{$id}'");

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