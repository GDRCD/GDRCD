<?php

class SchedaAbilita extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isPublic
     * @note Controlla se la scheda abilita' e' pubblica
     * @return bool
     */
    public function isPublic(): bool
    {
        return Functions::get_constant('SCHEDA_ABI_PUBLIC');
    }

    /**
     * @fn isAccessible
     * @note Controlla se le abilita sono accessibili
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg): bool
    {
        return ($this->isPublic() || $this->permissionViewAbilita() || Personaggio::isMyPg($id_pg));
    }

    /*** INDEX ***/

    /**
     * @fn indexSchedaAbilita
     * @note Indexing della scheda Abilita
     * @param string $op
     * @return string
     */
    public function indexSchedaAbilita(string $op): string
    {

        $page = match ($op) {
            'upgrade' => 'upgrade',
            'downgrade' => 'downgrade',
            default => 'list',
        };

        return Filters::out($page);
    }

    /**** PERMISSION ***/

    /**
     * @fn permissionViewStats
     * @note Controlla che si abbiano i permessi per visualizzare le abilita altrui
     * @return bool
     */
    public function permissionViewAbilita(): bool
    {
        return Permissions::permission('VIEW_SCHEDA_ABI');
    }

    /**** RENDERING ****/
    /**
     * @fn renderAbiPage
     * @note Elabora i dati per la pagina abilita'
     * @param int $id_pg
     * @return array
     */
    public function renderAbiPage(int $id_pg): array
    {

        $abilities = Abilita::getInstance()->getAllAbilita();
        $abilities_data = [];

        $cells = [
            'Nome',
            'Statistica',
            'Grado',
            'Comandi',
        ];

        foreach ( $abilities as $ability ) {

            $id = Filters::int($ability['id']);
            $stat_data = Statistiche::getInstance()->getStat($ability['statistica']);
            $stat_name = Filters::out($stat_data['nome']);
            $pg_abi_data = PersonaggioAbilita::getInstance()->getPgAbility($id, $id_pg, 'grado');
            $grado = Filters::int($pg_abi_data['grado']);

            $abi_extra_data = $this->LvlData($id, $grado);

            $abilities_data[] = [
                'id' => $id,
                'id_pg' => $id_pg,
                'nome' => Filters::out($ability['nome']),
                'stat' => $stat_name,
                'grado' => $grado,
                'upgrade_permission' => PersonaggioAbilita::getInstance()->permissionUpgradeAbilita($id_pg, $grado),
                'downgrade_permission' => PersonaggioAbilita::getInstance()->permissionDowngradeAbilita($grado),
                'extra_data' => $abi_extra_data,
                'extra_active' => Abilita::getInstance()->extraActive(),
                'requirement_active' => AbilitaRequisiti::getInstance()->requirementActive(),
            ];
        }

        return [
            'body_rows' => $abilities_data,
            'cells' => $cells,
            'table_title' => 'Abilità',
        ];

    }

    /**
     * @fn abilityPage
     * @note Renderizza la scheda abilita'
     * @param int $id_pg
     * @return string
     */
    public function abilityPage(int $id_pg): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/abilita',
            $this->renderAbiPage($id_pg)
        );
    }

    /**
     * @fn LvlData
     * @note Estrae i dati per la compilazione della descrizione del livello
     * @param int $abi
     * @param int $grado
     * @return array
     */
    public function LvlData(int $abi, int $grado): array
    {
        $abi_class = Abilita::getInstance();
        $extra_class = AbilitaExtra::getInstance();
        $req_class = AbilitaRequisiti::getInstance();

        # Filter passed data
        $abi = Filters::int($abi);
        $grado = Filters::int($grado);

        # Controllo se non ho il livello massimo consentito
        $new_grado = ($grado < $abi_class->abiLevelCap()) ? ($grado + 1) : 0;

        # Create return array
        $default_descr = $abi_class->getAbilita($abi, 'descrizione');
        $data = [
            'text' => Filters::html($default_descr['descrizione']),
            'requirement' => '',
            'lvl_extra_text' => '',
            'next_lvl_extra_text' => '',
            'next_price' => '',
        ];

        # Se i dati extra sono attivi
        if ( $abi_class->extraActive() ) {

            # Estraggo la descrizione extra attuale
            $actual_descr = $extra_class->getAbilitaExtra($abi, $grado, 'descrizione');

            # Se esiste la descrizione extra, la aggiungo
            if ( !empty($actual_descr['descrizione']) ) {
                $data['lvl_extra_text'] = Filters::html($actual_descr['descrizione']);
            }

            # Se non ho il livello massimo
            if ( $new_grado > 0 ) {

                # Estraggo la descrizione del livello successivo
                $next_descr = $extra_class->getAbilitaExtra($abi, $new_grado, 'descrizione,costo');
                $costo = Filters::int($next_descr['costo']);

                # Se esiste la descrizione extra del successivo, la aggiungo
                if ( !empty($next_descr['descrizione']) ) {
                    $data['next_lvl_extra_text'] = Filters::html($next_descr['descrizione']);
                }

                # Se il costo esiste ed e' maggiore di 0, lo setti, altrimenti usi il calcolo di default
                if ( !empty($costo) && ($costo > 0) ) {
                    $data['price'] = $costo;
                } else {
                    $data['price'] = $abi_class->defaultCalcUp($new_grado);
                }
            }
        }

        # Se i requisiti sono attivi
        if ( $abi_class->requirementActive() ) {

            # Li estraggo
            $requisiti = $req_class->getRequisitiByGrado($abi, $new_grado);

            # Per ogni requisito
            foreach ( $requisiti as $requisito ) {

                # Estraggo i suoi dati
                $tipo = Filters::int($requisito['tipo']);
                $rif = Filters::int($requisito['id_riferimento']);
                $rif_lvl = Filters::int($requisito['liv_riferimento']);

                # Compongo html in base al tipo
                switch ( true ) {
                    case $req_class->isTypeAbilita($tipo):
                        $req_data = $abi_class->getAbilita($rif, 'nome');
                        $nome = Filters::out($req_data['nome']);

                        $data['requirement'] .= " {$nome} {$rif_lvl}, ";

                        break;
                    case $req_class->isTypeStat($tipo):
                        if ( Statistiche::existStat($rif) ) {
                            $stat_class = Statistiche::getInstance();
                            $stat_data = $stat_class->getStat($rif);
                            $nome = Filters::out($stat_data['nome']);
                            $data['requirement'] .= " {$nome} {$rif_lvl}, ";
                        }
                        break;
                }
            }

            # Levo l'ultima ", " dal testo, partendo da destra
            $data['requirement'] = rtrim($data['requirement'], ", ");
        }

        # Ritorno i dati estratti
        return $data;
    }
}