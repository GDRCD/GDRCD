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

}