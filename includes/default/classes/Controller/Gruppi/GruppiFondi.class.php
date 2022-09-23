<?php

class GruppiFondi extends Gruppi
{

    private bool
        $active_founds;

    /**
     * @fn __construct
     * @note Costruttore della classe
     */
    protected function __construct()
    {
        parent::__construct();
        $this->active_founds = Functions::get_constant('GROUPS_FOUNDS');
    }

    /*** CONFIG ***/

    /**
     * @fn activeFounds
     * @note Ritorna se i fondi sono attivi
     * @return bool
     */
    public function activeFounds(): bool
    {
        return $this->active_founds;
    }

    /**** PERMESSI ****/

    /**
     * @fn permissionManageFounds
     * @note Controlla permessi sulla gestione dei fondi
     * @return bool
     */
    public function permissionManageFounds(): bool
    {
        return Permissions::permission('MANAGE_GROUPS_FOUNDS');
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getFound
     * @note Estrae un fondo preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getFound(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_fondi WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllFounds
     * @note Estrae tutti gli introiti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllFounds(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_fondi WHERE 1", []);
    }

    /**
     * @fn getAllFoundsByGroup
     * @note Estrae tutti gli introiti di un gruppo specifico
     * @param int $group
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllFoundsByGroup(int $group, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_fondi WHERE gruppo=:id", ['id' => $group]);
    }

    /**** LISTS ****/

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @return string
     * @throws Throwable
     */
    public function listFounds(): string
    {
        $founds = $this->getAllFounds();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $founds);
    }

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @param int $group
     * @return string
     * @throws Throwable
     */
    public function listFoundsByGroup(int $group): string
    {
        $founds = $this->getAllFoundsByGroup($group);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $founds);
    }

    /**
     * @fn listIntervalsTypes
     * @note Genera gli option per i tipi d'intervallo
     * @return string
     */
    public function listIntervalsTypes(): string
    {
        $intervals = [
            ["id" => 'months', 'nome' => "Mesi"],
            ["id" => 'days', 'nome' => "Giorni"],
        ];

        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $intervals);
    }

    /**** AJAX ****/

    /**
     * @fn ajaxFoundData
     * @note Estrae i dati di un fondo dinamicamente
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxFoundData(array $post): DBQueryInterface
    {
        $id = Filters::int($post['id']);
        return $this->getFound($id)->getData()[0];
    }

    /**** GESTIONE ****/

    /**
     * @fn NewFound
     * @note Inserisce un fondo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewFound(array $post): array
    {
        if ( $this->permissionManageFounds() ) {

            $nome = Filters::in($post['nome']);
            $group = Filters::int($post['gruppo']);
            $denaro = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);
            $last_exec = Filters::in($post['last_exec']);

            DB::queryStmt("INSERT INTO gruppi_fondi (nome, gruppo, denaro, intervallo, intervallo_tipo, last_exec) VALUES (:nome, :gruppo, :denaro, :intervallo, :intervallo_tipo, :last_exec)", [
                'nome' => $nome,
                'gruppo' => $group,
                'denaro' => $denaro,
                'intervallo' => $interval,
                'intervallo_tipo' => $interval_type,
                'last_exec' => $last_exec,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Fondo creato.',
                'swal_type' => 'success',
                'founds_list' => $this->listFounds(),
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
     * @fn ModFound
     * @note Aggiorna un fondo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModFound(array $post): array
    {
        if ( $this->permissionManageFounds() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $group = Filters::int($post['gruppo']);
            $denaro = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);

            DB::queryStmt("UPDATE gruppi_fondi SET nome=:nome, gruppo=:gruppo, denaro=:denaro, intervallo=:intervallo, intervallo_tipo=:intervallo_tipo WHERE id=:id", [
                'id' => $id,
                'nome' => $nome,
                'gruppo' => $group,
                'denaro' => $denaro,
                'intervallo' => $interval,
                'intervallo_tipo' => $interval_type,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Fondo modificato.',
                'swal_type' => 'success',
                'founds_list' => $this->listFounds(),
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
     * @fn DelFound
     * @note Cancella un fondo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelFound(array $post): array
    {
        if ( $this->permissionManageFounds() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM gruppi_fondi WHERE id=:id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Fondo eliminato.',
                'swal_type' => 'success',
                'founds_list' => $this->listFounds(),
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


    /*** CRONJOBS ***/

    /**
     * @fn assignFounds
     * @note Assegnazione fondi da cronjob
     * @return void
     * @throws Throwable
     */
    public function assignFounds(): void
    {

        if ( $this->activeFounds() ) {

            $groups = $this->getAllGroups();

            // Per ogni gruppo
            foreach ( $groups as $group ) {
                $total_given = 0;
                $group_id = Filters::int($group['id']);
                $group_name = Filters::in($group['nome']);

                // Estraggo i fondi
                $founds = $this->getAllFoundsByGroup($group_id);

                // Per ogni fondo
                foreach ( $founds as $found ) {
                    $found_id = Filters::int($found['id']);
                    $interval = Filters::int($found['interval']);
                    $interval_type = Filters::out($found['interval_type']);
                    $last_exec = Filters::out($found['last_exec']);

                    // Se devo assegnare il fondo
                    if ( CarbonWrapper::needExec($interval, $interval_type, $last_exec) ) {
                        $denaro = Filters::int($found['denaro']);
                        $total_given += $denaro;

                        DB::queryStmt("UPDATE gruppi SET denaro=denaro+:denaro WHERE id=:id", [
                            'id' => $group_id,
                            'denaro' => $denaro,
                        ]);
                        DB::queryStmt("UPDATE gruppi_fondi SET last_exec=NOW() WHERE id=:id", [
                            'id' => $found_id,
                        ]);
                    }
                }

                if ( $total_given > 0 ) {

                    $capi = GruppiRuoli::getInstance()->getAllGroupBoss($group_id);

                    foreach ( $capi as $capo ) {
                        $pg = Filters::in($capo['id']);

                        $titolo = Filters::in('Resoconto fondi assegnati oggi.');
                        $testo = Filters::in("Il gruppo '{$group_name}' ha ricevuto un totale di '{$total_given}' dollari.");

                        DB::queryStmt("INSERT INTO messaggi (mittente, destinatario, oggetto, testo, tipo) VALUES (:mittente, :destinatario, :oggetto, :testo, :tipo)", [
                            'mittente' => 'System',
                            'destinatario' => $pg,
                            'oggetto' => $titolo,
                            'testo' => $testo,
                            'tipo' => 0
                        ]);
                    }

                }

            }
        }
    }

}