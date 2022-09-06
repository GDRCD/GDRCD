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
     * @return mixed
     */
    public static function get_constant(string $name, bool $die_on_fail = true)
    {

        $name = Filters::in($name);

        $search = DB::query("SELECT const_name,val FROM config WHERE const_name='{$name}'", 'result', $die_on_fail);
        $num = DB::rowsNumber($search);

        if ( $num == 0 ) {
            $message = "Costante {$name} inesistente nella tabella config.";

            if ( $die_on_fail ) {
                die($message);
            } else {
                var_dump($message);
                return false;
            }
        } else if ( $num > 1 ) {
            $message = "Esistono più costanti con il nome '{$name}'. Correggere e riprovare.";

            if ( $die_on_fail ) {
                die($message);
            } else {
                var_dump($message);
                return false;
            }
        } else {
            $row = DB::query($search, 'fetch', $die_on_fail);
            return Filters::out($row['val']);
        }
    }

    /**
     * @fn set_constant
     * @note Funzione di estrazione delle costanti dal db
     * @param string $name
     * @param mixed $val
     * @return bool|void
     */
    public static function set_constant(string $name, $val)
    {

        $name = gdrcd_filter('in', $name);

        $search = gdrcd_query("SELECT const_name,val FROM config WHERE const_name='{$name}'", 'result');
        $num = gdrcd_query($search, 'num_rows');

        if ( $num == 0 ) {
            die("Costante {$name} inesistente nella tabella config.");
        } else if ( $num > 1 ) {
            die("Esistono più costanti con il nome '{$name}'. Correggere e riprovare.");
        } else {
            DB::query("UPDATE config SET val = '{$val}' WHERE const_name='{$name}'");
            return true;
        }
    }

    /**
     * @fn getPgId
     * @note Estrae l'id del pg dal suo nome
     * @param string $pg
     * @return int
     */
    public static function getPgId(string $pg): int
    {
        $pg = Filters::in($pg);
        $data = DB::query("SELECT id FROM personaggio WHERE nome='{$pg}' LIMIT 1");
        return Filters::int($data['id']);
    }

    /**
     * @fn getPermission
     * @note Estrae i permessi del personaggio specificato
     * @return int
     */
    public function getPermission(): int
    {
        return $this->permission;
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
     * @return false|int|mixed
     */
    public function getMyId()
    {
        return $this->me_id;
    }

    /**
     * @fn getPgList
     * @note Crea la select dei personaggi in land
     * @return string
     */
    public static function getPgList(int $selected = 0): string
    {

        $html = '';
        $selected = Filters::int($selected);
        $data = DB::query("SELECT id,nome FROM personaggio WHERE 1 ORDER BY nome", 'result');

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
     * @return bool|int|mixed|string
     */
    public static function getAllPgs()
    {
        return DB::query("SELECT id,nome FROM personaggio WHERE 1 ORDER BY nome", 'result');
    }

    /**
     * @fn redirect
     * @note Funzione di redirect della pagina
     * @param string $url
     * @param int|bool $tempo
     * @return void
     */
    public static function redirect(string $url, $tempo = false): void
    {
        if ( !headers_sent() && $tempo == false ) {
            header('Location:' . $url);
        } else if ( !headers_sent() && $tempo != false ) {
            header('Refresh:' . $tempo . ';' . $url);
        } else {
            if ( $tempo == false ) {
                $tempo = 0;
            }
            echo "<meta http-equiv=\"refresh\" content=\"" . $tempo . ";" . $url . "\">";
        }

        exit();
    }

    /**
     * @fn dateDifference
     * @note Calcolo differenza di ore fra la data/ora attuale e quella scelta
     * @param string $date_1 @format 'YYYY-MM-DD H:i:s'
     * @param string $date_2 @format 'YYYY-MM-DD H:i:s'
     * @param string $format
     * @return string
     */
    public static function dateDifference(string $date_1, string $date_2, string $format = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($format);
    }

    /**
     * @gn isBlacklistedIp
     * @note Ritorna true se l'ip fornito risulta essere in blacklist
     * @param string $ip
     * @return bool
     * @throws Throwable
     */
    public static function isBlacklistedIp(string $ip): bool
    {
        return !!count(
            DB::queryStmt(
                'SELECT * FROM blacklist WHERE ip = :address AND granted = 0',
                ['address' => $ip]
            )
        );
    }

    /**
     * @fn isAccountBanned
     * @note Permette di sapere se il personaggio è stato bannato
     * @param string $username lo username del personaggio da verificare
     * @return false|array false se il personaggio non è stato esiliato, altrimenti un array con i dettagli del ban
     * @throws Throwable
     */
    public static function isAccountBanned(string $username): false|array {
        $result = DB::queryStmt(
            'SELECT autore_esilio AS autore, esilio, motivo_esilio AS motivo FROM personaggio WHERE nome = :username',
            ['username' => $username]
        );

        return strtotime($result['esilio']) > time()? $result->current() : false;
    }
}