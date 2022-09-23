<?php

class SchedaChatOpzioni extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isAccessible
     * @note Controlla se le abilita sono accessibili
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg): bool
    {
        return Personaggio::isMyPg($id_pg);
    }

    /*** INDEX ***/

    /**
     * @fn indexSchedaChatOpzioni
     * @note Indexing della pagina opzioni chat in scheda
     * @param string $op
     * @return string
     */
    public function indexSchedaChatOpzioni(string $op): string
    {

        $page = match ($op) {
            default => 'update.php',
        };

        return Filters::out($page);
    }

    /**** RENDERING ****/

    /**
     * @fn optionsList
     * @note Creazione della sezione della gestione delle opzioni chat
     * @param int $pg
     * @return string
     * @throws Throwable
     */
    public function optionsList(int $pg): string
    {

        $html = '';

        $options = PersonaggioChatOpzioni::getInstance()->getAllOptionsWithValues($pg);

        foreach ( $options as $option ) {
            $name = Filters::out($option['nome']);
            $tipo = Filters::out($option['tipo']);
            $label = Filters::out($option['titolo']);
            $val = Filters::out($option['valore']);

            $html .= "<div class='single_input w-33'>";
            $html .= "<div class='label'>{$label}</div>";
            $html .= Gestione::getInstance()->inputByType($name, $val, $tipo);
            $html .= "</div>";
        }

        return $html;
    }

    /*** FUNCTIONS ***/

    /**
     * @fn updateOptions
     * @note Funzione per update delle opzioni chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public final function updateOptions(array $post): array
    {

        $pg = Filters::int($post['pg']);

        if ( $this->isAccessible($pg) ) {

            DB::queryStmt("DELETE FROM personaggio_chat_opzioni WHERE personaggio=:pg",[ 'pg' => $pg ]);

            $options_list = ChatOpzioni::getInstance()->getAllOptions();

            foreach ( $options_list as $option ) {
                $type = Filters::out($option['tipo']);
                $nome = Filters::out($option['nome']);
                $val = Filters::in($post[$nome]);

                $save_resp = $this->saveOption($nome, $type, $val, $pg);

                if ( !$save_resp ) {
                    $empty_const[] = $nome;
                }
            }

            if ( !empty($empty_const) ) {
                return $this->errorOptions($empty_const);
            } else {
                return [
                    'response' => true,
                    'swal_title' => 'Completato!',
                    'swal_message' => 'Opzioni modificate con successo!',
                    'swal_type' => 'success',
                ];
            }

        } else {
            return [
                'response' => false,
                'swal_title' => 'Permesso negato!',
                'swal_message' => 'Permesso negato!',
                'swal_type' => 'success',
            ];
        }
    }

    /**
     * @fn saveOption
     * @note Funzione di salvataggio e controllo tipologia del dato salvato
     * @param string $name
     * @param string $type
     * @param mixed $val
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    private function saveOption(string $name, string $type, mixed $val, int $pg): bool
    {

        $name = Filters::in($name);
        $type = Filters::out($type);

        switch ( $type ) {
            case 'bool':
                $val = !empty($val) ? 1 : 0;
                break;

            case 'int':
                if ( $val ) {
                    if ( !is_numeric($val) || is_float($val) ) {
                        return false;
                    } else {
                        $val = Filters::int($val);
                    }
                } else {
                    $val = '';
                }
                break;

            default:
            case 'string':
                $val = Filters::in($val);
                break;
        }

        return DB::query("INSERT INTO personaggio_chat_opzioni(personaggio,opzione,valore) VALUES ('{$pg}','{$name}','{$val}')");
    }

    /**
     * @fn errorConstant
     * @note Crea l'errore e la lista delle opzioni in errore
     * @param array $consts
     * @return array
     */
    private function errorOptions(array $consts): array
    {
        $resp = '';

        switch ( 'save' ) {
            case 'save':
                $resp = 'Errore durante l\'update delle opzioni, controllare i valori di: ';
                break;
        }

        foreach ( $consts as $e ) {

            $resp .= " {$e},";
        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => $resp,
            'swal_type' => 'error',
        ];
    }
}