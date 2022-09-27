<?php

/**
 * @class Functions
 * @note Classe contenitore delle funzioni principali degli helper
 */
class Functions extends BaseClass
{

    /**
     * @fn get_constant
     * @note Funzione di estrazione delle costanti dal db
     * @param string $name
     * @param bool $die_on_fail
     * @return string|bool
     * #TODO Rivedere die on fail
     * @throws Throwable
     */
    public static function get_constant(string $name, bool $die_on_fail = true): string|bool
    {

        $name = Filters::in($name);

        $search = DB::queryStmt("SELECT const_name,val FROM config WHERE const_name=:name", [
            'name' => $name,
        ]);
        $num = $search->getNumRows();

        if ( $num == 0 ) {
            $message = "Costante {$name} inesistente nella tabella config.";

            if ( $die_on_fail ) {
                die($message);
            } else {
                return false;
            }
        } else if ( $num > 1 ) {
            $message = "Esistono più costanti con il nome '{$name}'. Correggere e riprovare.";

            if ( $die_on_fail ) {
                die($message);
            } else {
                return false;
            }
        } else {
            return Filters::out($search['val']);
        }
    }

    /**
     * @fn set_constant
     * @note Funzione di estrazione delle costanti dal db
     * @param string $name
     * @param mixed $val
     * @return bool|null
     * @throws Throwable
     */
    public static function set_constant(string $name, mixed $val): ?bool
    {

        $name = gdrcd_filter('in', $name);

        $search = DB::queryStmt("SELECT const_name,val FROM config WHERE const_name=:name", [
            'name' => $name,
        ]);
        $num = $search->getNumRows();

        if ( $num == 0 ) {
            die("Costante {$name} inesistente nella tabella config.");
        } else if ( $num > 1 ) {
            die("Esistono più costanti con il nome '{$name}'. Correggere e riprovare.");
        } else {
            DB::queryStmt("UPDATE config SET val = :val WHERE const_name=:name",[
                'val' => $val,
                'name' => $name,
            ]);
            return true;
        }
    }

    /**
     * @fn getPgId
     * @note Estrae l'id del pg dal suo nome
     * @param string $pg
     * @return int
     * @throws Throwable
     */
    public static function getPgId(string $pg): int
    {
        $pg = Filters::in($pg);
        $data = DB::queryStmt("SELECT id FROM personaggio WHERE nome=:nome LIMIT 1",
        [
            'nome' => $pg,
        ]);
        return Filters::int($data['id']);
    }

    /**
     * @fn getMe
     * @note Estrae il nome del pg collegato
     * @return string
     */
    public function getMe(): string
    {
        return $this->me;
    }

    /**
     * @fn getMe
     * @note Estrae il nome del pg collegato
     * @return int
     */
    public function getMyId(): int
    {
        return $this->me_id;
    }

    /**
     * @fn getPgList
     * @note Crea la select dei personaggi in land
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public static function getPgList(int $selected = 0): string
    {

        $html = '';
        $selected = Filters::int($selected);
        $data = DB::queryStmt("SELECT id,nome FROM personaggio WHERE 1 ORDER BY nome", []);

        foreach ( $data as $row ) {
            $id = Filters::int($row['id']);
            $nome = Filters::out($row['nome']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }

    /**
     * @fn getAllPgs
     * @note Estrae la lista di tutti i pg
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getAllPgs(): DBQueryInterface
    {
        return DB::queryStmt("SELECT id,nome FROM personaggio WHERE 1 ORDER BY nome", []);
    }

    /**
     * @fn redirect
     * @note Funzione di redirect della pagina
     * @param string $url
     * @param bool|int $tempo
     * @return void
     */
    public static function redirect(string $url, bool|int $tempo = false): void
    {
        if ( !headers_sent() && !$tempo ) {
            header('Location:' . $url);
        } else if ( !headers_sent() && $tempo ) {
            header('Refresh:' . $tempo . ';' . $url);
        } else {
            if ( !$tempo ) {
                $tempo = 0;
            }
            echo "<meta http-equiv=\"refresh\" content=\"" . $tempo . ";" . $url . "\">";
        }

        exit();
    }
}