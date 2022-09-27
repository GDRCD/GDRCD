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
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getLog(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM logs WHERE id = :id", [
            'id' => $id
        ]);
    }

    /**
     * @fn getAllLogs
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $limit
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllLogs(int $limit = 500, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM log WHERE 1 LIMIT :limit", [
            'limit' => $limit
        ]);
    }

    /**
     * @fn getAllLogsByType
     * @note Ottieni tutti i logs dalla tabella logs
     * @param string $type
     * @param int $limit
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllLogsByType(string $type, int $limit = 500, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM log WHERE tipo=:type LIMIT :limit",[
            'type' => $type,
            'limit' => $limit
        ]);
    }

    /**
     * @fn getAllLogsByDestinatario
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $destinatario
     * @param int $limit
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllLogsByDestinatario(int $destinatario, int $limit = 500, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM log WHERE destinatario=:destinatario LIMIT :limit", [
            'destinatario' => $destinatario,
            'limit' => $limit
        ]);
    }

    /**
     * @fn getAllLogsByDestinatarioAndType
     * @note Ottieni tutti i logs dalla tabella logs
     * @param int $destinatario
     * @param string $type
     * @param int $limit
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllLogsByDestinatarioAndType(int $destinatario, string $type, int $limit = 500, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM log WHERE destinatario=:destinatario AND tipo=:type LIMIT :limit", [
            'destinatario' => $destinatario,
            'type' => $type,
            'limit' => $limit
        ]);
    }

    /**** PERMISSIONS ****/

    /**
     * @fn permissionViewLogs
     * @note Controlla se il personaggio può vedere i logs
     * @return bool
     */
    public function permissionViewLogs(): bool
    {
        return Permissions::permission('SCHEDA_VIEW_LOGS');
    }

    /**
     * @fn newLog
     * @note Inserisce un nuovo log
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public static function newLog(array $data): void
    {
        $autore = Filters::int($data['autore']);
        $tipo = Filters::in($data['tipo']);
        $testo = Filters::in($data['testo']);
        $destinatario = Filters::int($data['destinatario']);

        DB::queryStmt("INSERT INTO logs (autore, tipo, testo, destinatario) VALUES (:autore, :tipo, :testo, :destinatario)", [
            'autore' => $autore,
            'tipo' => $tipo,
            'testo' => $testo,
            'destinatario' => $destinatario
        ]);
    }

    /*** RENDER ***/

    /**
     * @fn renderLogTable
     * @note Elabora e restituisce la tabella dei logs
     * @param int $id_pg
     * @param string $title
     * @param int $type
     * @param int $limit
     * @return array
     * @throws Throwable
     */
    public function renderLogTable(int $id_pg, int $type, int $limit, string $title): array
    {

        $logs = Log::getInstance()->getAllLogsByDestinatarioAndType($id_pg, $type, $limit);
        $logs_data = [];

        $cells = [
            'Causale',
            'Destinatario',
            'Data',
            'Autore',
        ];

        foreach ( $logs as $log ) {

            $id = Filters::int($log['id']);
            $autore = Filters::int($log['autore']);
            $destinatario = Filters::int($log['destinatario']);

            $logs_data[] = [
                'id' => $id,
                'testo' => substr(Filters::out($log['testo']), 0, 30),
                'title' => Filters::out($log['testo']),
                'autore' => Personaggio::nameFromId($autore),
                'destinatario' => Personaggio::nameFromId($destinatario),
                'creato_il' => Filters::date($log['creato_il'], 'h:i:s d/m/Y'),
            ];
        }

        return [
            'body_rows' => $logs_data,
            'cells' => $cells,
            'table_title' => $title,
        ];

    }

    /**
     * @fn abilityPage
     * @note Renderizza la scheda abilita'
     * @param int $id_pg
     * @param int $type
     * @param int $limit
     * @param string $title
     * @return string
     * @throws Throwable
     */
    public function logTable(int $id_pg, int $type, int $limit = 500, string $title = "Log"): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'log/table',
            $this->renderLogTable($id_pg, $type, $limit, $title)
        );
    }

}