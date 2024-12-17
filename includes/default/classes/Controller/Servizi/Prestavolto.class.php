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
            "SELECT prestavolto.nome as prestavolto_nome, prestavolto.cognome as prestavolto_cognome, personaggio.nome, personaggio.cognome
                    
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
                "id" => Filters::int($pg_data['id']),
                "name_pv" => Filters::out($pg_data['prestavolto_nome']),
                'surname_pv' => Filters::out($pg_data['prestavolto_cognome']),
                "name" => Filters::out($pg_data['nome']),
                'surname' => Filters::out($pg_data['cognome']),
                 
            ];
        }

        $cells = [
           'Nome Prestavolto',
            'Cognome Prestavolto',
           
            'Nome',
            'Cognome',
           
        ];

        return Template::getInstance()->startTemplate()->renderTable('servizi/prestavolto/results', [
            'body_rows' => $pgs,
            'cells' => $cells,
            'links' => [],
        ]);
    }

}