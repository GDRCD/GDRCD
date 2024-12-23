<?php

class Notifiche extends BaseClass
{

    private bool $enabled;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();
        $this->enabled = Functions::get_constant('NOTIFICATIONS_ENABLED');
    }

    /*** GETTERS */

    /**
     * @fn isEnabled
     * @note Controlla se la funzione è abilitata
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /*** ROUTING ***/

    /**
     * @fn loadPage
     * @note Routing delle pagine delle notifiche
     * @param string $op
     * @return string
     */
    public function loadPage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            default => 'view.php',
        };
    }

    /*** TABLES HELPERS **/

    /**
     * @fn getNotification
     * @note Estrae i dati di una notifica
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getNotification(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT $val FROM personaggio_notifiche WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn countNewNotifications
     * @note Conta le nuove notifiche
     * @param int $pg
     * @return int
     * @throws Throwable
     */
    public function countNewNotifications(int $pg): int
    {
        $results = DB::queryStmt("SELECT count(id) as count FROM personaggio_notifiche WHERE personaggio=:pg AND letto=0", [
            'pg' => $pg,
        ]);
        return $results->getData()[0]['count'];
    }

    /**
     * @fn listNotifications
     * @note Estrae la lista delle notifiche
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function listNotifications(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT $val FROM personaggio_notifiche WHERE personaggio=:pg ORDER BY creato_il DESC LIMIT 20 ", [
            'pg' => $pg,
        ]);
    }

    /**
     * @fn listNotifications
     * @note Estrae la lista delle notifiche
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function listAllNotifications(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT $val FROM personaggio_notifiche WHERE personaggio=:pg ORDER BY creato_il DESC", [
            'pg' => $pg,
        ]);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxStatusData
     * @note Estrae i dati di uno stato
     * @return array
     * @throws Throwable
     */
    public function ajaxNotificationsListData(): array
    {
        return ['list' => $this->renderNotificationsList()];
    }

    /**
     * @fn ajaxStatusData
     * @note Estrae i dati di uno stato
     * @return array
     * @return DBQueryInterface[]
     * @throws Throwable
     */
    public function ajaxCountNewNotifications(): array
    {
        return ['new_notifications' => $this->countNewNotifications($this->me_id)];
    }


    /**** LIST ****/

    /**
     * @fn renderStatusOnline
     * @note Visualizza le risposte nello status online
     * @return string
     * @throws Throwable
     */
    public function renderNotificationsList(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'notifiche/notifiche',
            ['notifications' => $this->composeNotificationsListData($this->me_id)]
        );
    }

    /**
     * @fn renderStatusOnline
     * @note Visualizza le risposte nella pagina delle notifiche
     * @return string
     * @throws Throwable
     */
    public function renderNotificationsPage(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'notifiche/view',
            ['notifications' => $this->composeNotificationsPageData($this->me_id)]
        );
    }

    /**
     * @fn composeNotificationsListData
     * @note Elabora i dati della lista delle notifiche
     * @param int $pg
     * @return array
     * @throws Throwable
     */
    public function composeNotificationsListData(int $pg): array
    {
        $result = [];

        $pg = Filters::int($pg);
        $notifications = $this->listNotifications($pg)->getData();

        foreach ( $notifications as $notification ) {
            $result[] = [
                'testo' => Filters::out($notification['testo']),
                'titolo' => Filters::out($notification['titolo']),
                'letto' => Filters::int($notification['letto']),
                'creato_il' => CarbonWrapper::format($notification['creato_il'], 'H:i d/m/y'),
            ];
        }

        return $result;
    }

    /**
     * @fn composeNotificationsPageData
     * @note Elabora i dati della pagina delle notifiche
     * @param int $pg
     * @return array
     * @throws Throwable
     */
    public function composeNotificationsPageData(int $pg): array
    {
        $result = [];

        $pg = Filters::int($pg);
        $notifications = $this->listAllNotifications($pg)->getData();

        foreach ( $notifications as $notification ) {
            $result[] = [
                'testo' => Filters::out($notification['testo']),
                'titolo' => Filters::out($notification['titolo']),
                'letto' => Filters::int($notification['letto']),
                'creato_il' => CarbonWrapper::format($notification['creato_il']),
            ];
        }

        return $result;
    }

    /*** FUNCTIONS ***/

    public function setAllNotificationsRead(): array
    {
        DB::queryStmt("UPDATE personaggio_notifiche SET letto=1 WHERE personaggio=:pg", [
            'pg' => $this->me_id,
        ]);

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Tutte le notifiche sono state segnate come lette',
            'swal_type' => 'success',
        ];
    }

}