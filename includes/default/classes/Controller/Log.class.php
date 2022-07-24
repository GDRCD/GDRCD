<?php

class Log extends BaseClass
{
    protected function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getLog
     * @note Ottieni un log dalla tabella logs
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getLog(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM log WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllLogs
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $limit
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllLogs(int $limit = 500,string $val = '*')
    {
        return DB::query("SELECT {$val} FROM log WHERE 1 LIMIT {$limit}", 'result');
    }

    /**
     * @fn getAllLogsByType
     * @note Ottieni tutti i logs dalla tabella logs
     * @param string $type
     * @param int $limit
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllLogsByType(string $type, int $limit = 500, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM log WHERE tipo='{$type}' LIMIT {$limit}", 'result');
    }

    /**
     * @fn getAllLogsByDestinatario
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $destinatario
     * @param int $limit
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllLogsByDestinatario(int $destinatario, int $limit = 500, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM log WHERE destinatario='{$destinatario}' LIMIT {$limit}", 'result');
    }

    /**
     * @fn getAllLogsByDestinatarioAndType
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $destinatario
     * @param string $type
     * @param int $limit
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllLogsByDestinatarioAndType(int $destinatario, string $type, int $limit = 500, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM log WHERE destinatario='{$destinatario}' AND tipo='{$type}' LIMIT {$limit}", 'result');
    }

    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageGenders
     * @note Controlla se il personaggio può gestire i generi
     * @return bool
     */
    public function permissionViewLogs(): bool
    {
        return Permissions::permission('VIEW_LOGS');
    }

    /**
     * @fn newGender
     * @note Inserisce un nuovo genere
     * @param array $data
     * @return void
     */
    public static function newLog(array $data): void
    {
        $autore = Filters::int($data['autore']);
        $tipo = Filters::in($data['tipo']);
        $testo = Filters::in($data['testo']);
        $destinatario = Filters::int($data['destinatario']);

        DB::query("INSERT INTO log (autore, tipo, testo, destinatario) VALUES ('{$autore}', '{$tipo}', '{$testo}', '{$destinatario}')");
    }
}