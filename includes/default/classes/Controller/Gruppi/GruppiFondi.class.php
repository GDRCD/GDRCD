<?php

class GruppiFondi extends Gruppi
{

    private
        $active_founds;

    protected function __construct()
    {
        parent::__construct();
        $this->active_founds = Functions::get_constant('GROUPS_FOUNDS');
    }

    /*** CONFIG ***/

    public function activeFounds()
    {
        return $this->active_founds;
    }

    /** PERMESSI */

    /**
     * @fn permissionManageFounds
     * @note Controlla permessi sulla gestione dei fondi
     * @return bool
     */
    public function permissionManageFounds(): bool
    {
        return Permissions::permission('MANAGE_GROUPS_FOUNDS');
    }


    /** TABLE HELPERS */

    /**
     * @fn getFound
     * @note Estrae un fondo preciso
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getFound(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_fondi WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllFounds
     * @note Estrae tutti gli introiti
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllFounds(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_fondi WHERE 1", 'result');
    }

    /**
     * @fn getAllFoundsByGroup
     * @note Estrae tutti gli introiti di un gruppo specifico
     * @param int $group
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllFoundsByGroup(int $group, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_fondi WHERE gruppo='{$group}'", 'result');
    }

    /** LISTS */

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @return string
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
     */
    public function listFoundsByGroup(int $group): string
    {
        $founds = $this->getAllFoundsByGroup($group);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $founds);
    }

    /**
     * @fn listIntervalsTypes
     * @note Genera gli option per i tipi di intervallo
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
    /** AJAX */

    /**
     * @fn ajaxFoundData
     * @note Estrae i dati di un fondo dinamicamente
     * @param array $post
     * @return array|bool|int|string
     */
    public function ajaxFoundData(array $post): array
    {
        $id = Filters::int($post['id']);
        return $this->getFound($id);
    }

    /** GESTIONE */

    /**
     * @fn NewFound
     * @note Inserisce un fondo
     * @param array $post
     * @return array
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

            DB::query("INSERT INTO gruppi_fondi (`nome`,`gruppo`,`denaro`,`interval`,`interval_type`,`last_exec`)  
                        VALUES ('{$nome}','{$group}','{$denaro}','{$interval}','{$interval_type}','{$last_exec}') ");

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

            DB::query("UPDATE  gruppi_fondi 
                SET `nome` = '{$nome}', 
                    `gruppo`='{$group}',
                    `denaro` ='{$denaro}',
                    `interval`='{$interval}',
                    `interval_type`='{$interval_type}'
                    WHERE id='{$id}'");

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
     */
    public function DelFound(array $post): array
    {
        if ( $this->permissionManageFounds() ) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi_fondi WHERE id='{$id}'");

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

    /***
     * @fn assignFounds
     * @note Assegnazione fondi da cronjob
     * @return void
     */
    public function assignFounds()
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
                        DB::query("UPDATE gruppi SET denaro=denaro+'{$denaro}' WHERE id='{$group_id}' LIMIT 1");
                        DB::query("UPDATE gruppi_fondi SET last_exec=NOW() WHERE id='{$found_id}' LIMIT 1");
                    }
                }

                if ( $total_given > 0 ) {

                    $capi = GruppiRuoli::getInstance()->getAllGroupBoss($group_id);

                    foreach ( $capi as $capo ) {
                        // TODO Sostituire pg name con pg id all'invio messaggio
                        $pg = Filters::in($capo['nome']);

                        $titolo = Filters::in('Resoconto fondi assegnati oggi.');
                        $testo = Filters::in("Il gruppo '{$group_name}' ha ricevuto un totale di '{$total_given}' dollari.");
                        DB::query("INSERT INTO messaggi(mittente,destinatario,tipo,oggetto,testo) VALUES('System','{$pg}',0,'{$titolo}','{$testo}') ");
                    }

                }

            }
        }
    }

}