<?php

class SchedaTransazioni extends Scheda
{
    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**** PERMISSION ***/

    /**
     * @fn permissionViewTransactions
     * @note Permessi per visualizzazione delle transazioni
     * @param int $id_pg
     * @return bool
     */
    public function permissionViewTransactions(int $id_pg): bool
    {
        return (Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_VIEW_TRANSACTIONS'));

    }

    /**
     * @fn viewExpPermission
     * @note Controllo permessi per visualizzazione esperienza scheda
     * @return bool
     */
    public function viewExpPermission(): bool
    {
        return Permissions::permission('SCHEDA_EXP_VIEW');
    }

    /**
     * @fn manageExpPermission
     * @note Controllo permessi per singola assegnazione esperienza scheda
     * @return bool
     */
    public function manageExpPermission(): bool
    {
        return Permissions::permission('SCHEDA_EXP_MANAGE');
    }

    /***** RENDER ***/

    /**
     * @fn renderPgExpLog
     * @note Lista degli ultimi log di tipo esperienza
     * @param $pg
     * @return array
     */
    public function renderPgExpLog($pg): array
    {
        $logs = Log::getInstance()->getAllLogsByDestinatarioAndType($pg, PX, 10);
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
            'table_title' => 'Esperienza',
        ];
    }

    /**
     * @fn expTable
     * @note Tabella dell'esperienza del pg
     * @param $pg
     * @return string
     */
    public function expTable($pg): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/esperienza',
            $this->renderPgExpLog($pg)
        );
    }

    /*** FUNCTIONS ***/

    /**
     * @fn addManualExp
     * @note Aggiunge esperienza manuale
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function addManualExp(array $post): array
    {
        if ( $this->manageExpPermission() ) {

            $pg = Filters::in($post['pg']);
            $pg_name = Personaggio::nameFromId($pg);
            $causale = Filters::in($post['causale']);
            $px = Filters::int($post['px']);

            if ( !empty($causale) && ($px != 0) && !empty($pg) ) {

                Personaggio::updatePgData(
                    $pg,
                    'esperienza = esperienza + :exp',
                    [
                        'exp' => $px,
                    ]
                );

                Log::newLog([
                    "autore" => $this->me_id,
                    "destinatario" => $pg,
                    "tipo" => PX,
                    "testo" => "Assegnati {$px}px a {$pg_name} per '{$causale}'",
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Esperienza assegnata correttamente.',
                    'swal_type' => 'success',
                    'new_template' => Log::getInstance()->logTable($pg, PX, 10, 'Esperienza'),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Compila tutti i campi.',
                    'swal_type' => 'error',
                ];
            }

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