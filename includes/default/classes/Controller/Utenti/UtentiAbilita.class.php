<?php


class UtentiAbilita extends BaseClass{

    /**
     * @fn abilityPage
     * @note Renderizza la scheda abilita'
     * @return string
     * @throws Throwable
     */
    public function abilityPage(): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'utenti/abilita',
            $this->renderAbiPage()
        );
    }

    /**
     * @fn renderAbiPage
     * @note Elabora i dati per la pagina abilita' utenti
     * @return array
     * @throws Throwable
     */
    public function renderAbiPage(): array
    {

        $abilities = Abilita::getInstance()->getAllAbilita();
        $abilities_data = [];

        $cells = [
            'Nome',
            'Statistica',
        ];

        $extra_active = AbilitaExtra::getInstance()->extraActive();
        $ability_lev_cap = Abilita::getInstance()->abiLevelCap();

        foreach ( $abilities as $ability ) {

            $id = Filters::int($ability['id']);
            $stat_data = Statistiche::getInstance()->getStat($ability['statistica']);
            $stat_name = Filters::out($stat_data['nome']);

            $extra = [];

            if($extra_active){

                for($i = 1; $i <= $ability_lev_cap; $i++){

                    $extra_data = AbilitaExtra::getInstance()->getAbilitaExtra($id, $i);

                    if($extra_data->getNumRows() > 0){
                        $extra[$i] = [
                            'id' => $id,
                            'level' => $i,
                            "description" => Filters::out($extra_data['descrizione']),
                            "price" => Filters::out($extra_data['costo']),
                        ];
                    }
                }


            }


            $abilities_data[] = [
                'id' => $id,
                'name' => Filters::out($ability['nome']),
                'description'=> Filters::text($ability['descrizione']),
                'stat' => $stat_name,
                'extra' => $extra,
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

}