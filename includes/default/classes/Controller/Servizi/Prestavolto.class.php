<?php

class Prestavolto extends BaseClass
{
    /**
     * @fn ajaxSearch
     * @note Ritorna la lista dei personaggi che soddisfano i criteri di ricerca
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxSearch(array $post): array
    {

        $name = Filters::in($post['name']);
        $order_for = Filters::out($post['order_for']);

        $name_search = ($name) ? " AND prestavolto.nome LIKE \"%{$name}%\" " : '';
        $order_by_search = match ($order_by) {
            default => 'prestavolto.nome',
            'surname' => 'prestavolto.cognome',
        };
        $order_for_search = match ($order_for) {
            "DESC" => "DESC",
            default => 'ASC'
        };
        
        $pgs_list = DB::queryStmt(
            "SELECT prestavolto.nome as prestavolto_nome, prestavolto.cognome as prestavolto_cognome, personaggio.nome, personaggio.cognome, prestavolto.fonte,
            prestavolto.condivisibile, prestavolto.condivisibile_immagine, prestavolto.condivisibile_descrizione
                    
                    FROM prestavolto
                    LEFT JOIN personaggio ON personaggio.id = prestavolto.personaggio
                    
                    WHERE 1 = 1
                    {$name_search}
                     
                    ORDER BY {$order_by_search} {$order_for_search}
                    LIMIT 500 ", []);

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Ricerca completata.',
            'swal_type' => 'success',
            'search' => $this->renderResults($pgs_list),
        ];

    }

    /**
     * @fn renderResults
     * @note Ritorna il markup HTML della lista dei personaggi
     * @param $pgs_list
     * @return string
     */
    public function renderResults($pgs_list): string
    {

        $pgs = [];
        foreach ( $pgs_list as $pg_data ) {
 
            $pgs[] = [
                'id' => Filters::int($pg_data['id']),
                'name_pv' => Filters::out($pg_data['prestavolto_nome']),
                'surname_pv' => Filters::out($pg_data['prestavolto_cognome']),
                'name' => Filters::out($pg_data['nome']),
                'surname' => Filters::out($pg_data['cognome']),
                'fonte'=> Filters::out($pg_data['fonte']),

                'condivisibile' => (Filters::out($pg_data['condivisibile']))?'Si':'No',
                'condivisibile_immagine' => Filters::out($pg_data['condivisibile_immagine']),
                'condivisibile_descrizione' => Filters::out($pg_data['condivisibile_descrizione']),
                'condivisione_attiva'=> SchedaPrestavolto::getInstance()->PvShareable(),
                'condivisione_img_attiva'=> SchedaPrestavolto::getInstance()->PvHaveImage(),

                 
            ];
        }

        $cells = [
            'Nome Prestavolto',
            'Cognome Prestavolto',
            'Fonte',
            'Nome',
            'Cognome',
            'Condivisibile',
            'Immagine',
            
            'Condizioni condivisione',
        ];

        return Template::getInstance()->startTemplate()->renderTable('servizi/prestavolto/results', [
            'body_rows' => $pgs,
            'cells' => $cells,
            'links' => [],
        ]);
    }
    
    /**
     * @fn getPvData
     * @note Ottiene i dati di un prestavolto
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPvData(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM prestavolto WHERE personaggio=:id LIMIT 1", ['id' => $pg]);
    }
   

}