<?php

class SchedaStats extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isPublic
     * @note Controlla se la scheda statistiche e' pubblica
     * @return bool
     */
    public function isPublic():bool{
        return Functions::get_constant('SCHEDA_STATS_PUBLIC');
    }

    /**
     * @fn isAccessible
     * @note Controlla se le statistiche sono accessibili
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg):bool{
        return ($this->isPublic() || $this->permissionViewStats() || Personaggio::isMyPg($id_pg));
    }

    /*** INDEX ***/

    /**
     * @fn indexSchedaStats
     * @note Indexing della scheda statistiche
     * @param string $op
     * @return string
     */
    public function indexSchedaStats(string $op):string{

        switch ($op){
            case 'upgrade':
                $page = 'upgrade';
                break;
            case 'downgrade':
                $page = 'downgrade';
                break;
            default:
                $page = 'list';
                break;
        }

        return Filters::out($page);
    }

    /**** PERMISSION ***/

    /**
     * @fn permissionViewStats
     * @note Controlla che si abbiano i permessi per visualizzare le statistiche altrui
     * @return bool
     */
    public function permissionViewStats(): bool
    {
        return Permissions::permission('VIEW_SCHEDA_STATS');
    }



    /*** FUNCTIONS **/

    /**** RENDERING ****/

    /**
     * @fn renderStatsPage
     * @note Elabora i dati della scheda statistiche
     * @param int $id_pg
     * @return array
     */
    public function renderStatsPage(int $id_pg): array
    {

        $stats = PersonaggioStats::getInstance()->getPgAllStats($id_pg);
        $stats_data = [];


        $cells = [
            'Nome',
            'Grado',
            'Comandi'
        ];

        foreach ($stats as $stat) {

            $id = Filters::int($stat['id']);

            $stats_data[] = [
                'id' => $id,
                'id_pg' => $id_pg,
                'nome' => Filters::out($stat['nome']),
                'descrizione' => Filters::text($stat['descrizione']),
                'valore' => Filters::int($stat['valore']),
                'upgrade_permission' => PersonaggioStats::permissionUpgradeStats() && PersonaggioStats::isPgStatUpgradable($id, $id_pg),
                'downgrade_permission' => PersonaggioStats::permissionDowngradeStats() && PersonaggioStats::isPgStatDowngradable($id, $id_pg)
            ];
        }


        return [
            'body' => 'scheda/stats',
            'body_rows' => $stats_data,
            'cells' => $cells,
            'table_title' => 'Statistiche',
        ];

    }

    /**
     * @fn abilityPage
     * @note Renderizza la scheda abilita'
     * @param int $id_pg
     * @return string
     */
    public function statsPage(int $id_pg): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/stats',
            $this->renderStatsPage($id_pg)
        );
    }


}