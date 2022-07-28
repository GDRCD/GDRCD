<?php

class MeteoStagioni extends Meteo
{

    /**** PERMESSI ****/

    /**
     * @fn permissionManageSeasons
     * @note Controlla se si hanno i permessi per gestire le stagioni meteo
     * @return bool
     */
    public function permissionManageSeasons(): bool
    {
        return Permissions::permission('MANAGE_WEATHER_SEASONS');
    }

    /*** TABLE HELPER ***/

    /**
     * @fn getAllSeason
     * @note Estrae lista delle stagioni
     * @return bool|int|mixed|string
     */
    public function getAllSeason(string $val = '*')
    {
        return DB::query("SELECT {$val}  FROM meteo_stagioni WHERE 1 ORDER BY nome", 'result');
    }

    /**
     * @fn getSeason
     * @note Estrae una stagione
     * @return bool|int|mixed|string
     */
    public function getSeason(int $id, string $val = '*')
    {
        $id = Filters::int($id);
        return DB::query("SELECT {$val} FROM meteo_stagioni WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllSeasonCondition
     * @note Estrae tutte le condizioni meteo di una stagione
     * @param int $id
     * @param string $val
     * @return array
     */
    public function getAllSeasonCondition(int $id, string $val = 'meteo_stagioni_condizioni.*, meteo_condizioni.*')
    {
        $output = [];
        $stmt = DB::query("SELECT {$val} FROM meteo_stagioni_condizioni LEFT JOIN meteo_condizioni ON meteo_stagioni_condizioni.condizione = meteo_condizioni.id WHERE meteo_stagioni_condizioni.stagione='{$id}'", 'result');

        if (DB::query($stmt, 'num_rows')) {
            while ($output[] = DB::query($stmt, 'fetch'));
            DB::query($stmt,'free');
        }

        return $output;
    }

    /**
     * @fn getCurrentSeason
     * @note Estrae la stagione corrente
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getCurrentSeason(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_stagioni WHERE data_fine > NOW() AND data_inizio < NOW() LIMIT 1");
    }

    /**** RENDER ***/

    /**
     * @fn loadServicePageEsiti
     * @note Routing delle pagine di servizi
     * @param string $op
     * @return string
     */
    public function loadManagePage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            default:
                $page = 'gestione_stagioni_view.php';
                break;

            case 'new':
                $page = 'gestione_stagioni_new.php';
                break;

            case 'edit':
                $page = 'gestione_stagioni_edit.php';
                break;

            case 'conditions':
                $page = 'gestione_stagioni_condizioni.php';
                break;
        }

        return $page;
    }

    /**
     * @fn esitiListManagement
     * @note Render lista gestione delle stagioni
     * @return string
     */
    public function seasonListManagement(): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllSeason('*', 'ORDER BY closed ASC,data ASC');
        return $template->renderTable(
            'gestione/meteo/stagioni/list',
            $this->renderSeasonList($list)
        );
    }

    /**
     * @fn renderSeasonList
     * @note Sotto-funzione per regole di renderizzazione della lista stagioni in gestione
     * @param object $list
     * @return array
     */
    public function renderSeasonList(object $list): array
    {
        $row_data = [];

        foreach ( $list as $row ) {

            $array = [
                'id' => Filters::int($row['id']),
                'name' => Filters::out($row['nome']),
                'min' => Filters::out($row['minima']),
                'max' => Filters::out($row['massima']),
                'date_start' => Filters::date($row['data_inizio'], 'd/m/Y'),
                'sunrise' => Filters::date($row['alba'], 'H:i'),
                'sunset' => Filters::date($row['tramonto'], 'H:i'),
                'meteo_season_permission' => $this->permissionManageSeasons(),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
            'Minima',
            'Massima',
            'Data Inizio',
            'Alba',
            'Tramonto',
            'Controlli',
        ];
        $links = [
            ['href' => "/main.php?page=gestione/meteo/stagioni/gestione_stagioni_index&op=new", 'text' => 'Nuova stagione'],
            ['href' => "/main.php?page=gestione/meteo/stagioni/gestione_stagioni_index", 'text' => 'Indietro'],
        ];
        return [
            'body' => 'gestione/meteo/stagioni/list',
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
        ];
    }

    /**
     * @fn esitiListManagement
     * @note Render lista gestione delle stagioni
     * @param int $id
     * @return string
     */
    public function seasonConditionsManageList(int $id): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllSeasonCondition($id);
        return $template->renderTable(
            'gestione/meteo/stagioni/condition_list',
            $this->renderSeasonConditionList($list)
        );
    }

    /**
     * @fn renderSeasonList
     * @note Sotto-funzione per regole di renderizzazione della lista stagioni in gestione
     * @param array $list
     * @return array
     */
    public function renderSeasonConditionList(array $list): array
    {

        $row_data = [];

        foreach ( $list as $row ) {

            $array = [
                'name' => Filters::out($row['nome']),
                'percentage' => Filters::out($row['percentuale']),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
            'Percentuale',
        ];
        $links = [
            ['href' => "/main.php?page=gestione_meteo_stagioni&op=new", 'text' => 'Nuova stagione'],
            ['href' => "/main.php?page=gestione_meteo_stagioni", 'text' => 'Indietro'],
        ];
        return [
            'body' => 'gestione/meteo/stagioni/condition_list',
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
        ];
    }

    /**** FUNCTIONS ****/

    /**
     * @fn diffselectSeason
     * @note Select degli stati climatici non presenti nella stagione
     * @param array $array
     * @return string
     */
    public function diffselectSeason(array $array): string
    {
        $option = "";
        $stagioni = MeteoStagioni::getInstance()->getAllSeason();
        foreach ( $stagioni as $item ) {
            $option .= "<div class='form_field'>";
            if ( in_array($item['id'], $array) ) {
                $option .= "<input type='checkbox' name='stagioni[]' checked value='{$item['id']}'></div>";
            } else {
                $option .= "<input type='checkbox' name='stagioni[]' value='{$item['id']}'></div>";
            }

            $option .= "<div class='form_label'>{$item['nome']}</div>";
        }
        return $option;
    }

    /**** GESTIONE ****/

    /**
     * @fn NewSeason
     * @note Inserisce una stagione
     * @param array $post
     * @return void
     */
    public function NewSeason(array $post): array
    {

        if ( $this->permissionManageSeasons() ) {
            $nome = Filters::in($post['nome']);
            $minima = Filters::in($post['minima']);
            $massima = Filters::in($post['massima']);
            $data_inizio = Filters::in($post['data_inizio']);
            $data_fine = Filters::in($post['data_fine']);
            $alba = Filters::in($post['alba']);
            $tramonto = Filters::in($post['tramonto']);

            DB::query("INSERT INTO meteo_stagioni (nome,minima,massima, data_inizio,data_fine, alba, tramonto )  VALUES ('{$nome}', '{$minima}' , '{$massima}', '{$data_inizio}', '{$data_fine}', '{$alba}', '{$tramonto}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stagione creata.',
                'swal_type' => 'success',
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
     * @fn editSeason
     * @note Aggiorna una stagione
     * @param array $post
     * @return void
     */
    public function ModSeason(array $post): array
    {
        if ( $this->permissionManageSeasons() ) {
            $id = Filters::in($post['id']);

            $nome = Filters::in($post['nome']);
            $minima = Filters::in($post['minima']);
            $massima = Filters::in($post['massima']);
            $data_inizio = Filters::in($post['data_inizio']);
            $data_fine = Filters::in($post['data_fine']);
            $alba = Filters::in($post['alba']);
            $tramonto = Filters::in($post['tramonto']);
            DB::query("UPDATE  meteo_stagioni 
                SET nome = '{$nome}',minima='{$minima}', massima='{$massima}', data_inizio='{$data_inizio}',data_fine='{$data_fine}', alba='{$alba}', tramonto='{$tramonto}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stagione modificata.',
                'swal_type' => 'success',
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
     * @fn deleteSeason
     * @note Cancella una stagione
     * @param array $post
     * @return array
     */
    public function DelSeason(array $post): array
    {
        if ( $this->permissionManageSeasons() ) {

            $id = Filters::in($post['id']);
            DB::query("DELETE FROM meteo_stagioni WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stagione eliminata.',
                'swal_type' => 'success',
                'stagioni_list' => $this->seasonListManagement(),
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
     * @fn AssignCondition
     * @note Assegna una condizione ad una stagione
     * @param array $post
     * @return array
     */
    public function AssignCondition(array $post): array
    {

        if ( $this->permissionManageSeasons() ) {

            $id = Filters::int($post['id']);
            $condizione = Filters::int($post['condizione']);
            $percentuale = Filters::int($post['percentuale']);

            DB::query("DELETE FROM meteo_stagioni_condizioni WHERE stagione='{$id}' AND condizione='{$condizione}'");
            DB::query("INSERT INTO meteo_stagioni_condizioni(stagione,condizione,percentuale) VALUES('{$id}','{$condizione}','{$percentuale}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione associata.',
                'swal_type' => 'success',
                'stagioni_conditions' => $this->seasonConditionsManageList($id),
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
     * @fn RemoveCondition
     * @note Rimuove una condizione da una stagione
     * @param array $post
     * @return array
     */
    public function RemoveCondition(array $post): array
    {

        if ( $this->permissionManageSeasons() ) {

            $id = Filters::int($post['id']);
            $condizione = Filters::int($post['condizione']);

            DB::query("DELETE FROM meteo_stagioni_condizioni WHERE stagione='{$id}' AND condizione='{$condizione}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione rimossa.',
                'swal_type' => 'success',
                'stagioni_conditions' => $this->seasonConditionsManageList($id),
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