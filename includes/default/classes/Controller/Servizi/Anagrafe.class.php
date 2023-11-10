<?php

class Anagrafe extends BaseClass
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
        $race = Filters::int($post['race']);
        $gender = Filters::int($post['gender']);
        $order_by = Filters::out($post['order_by']);
        $order_for = Filters::out($post['order_for']);

        $name_search = ($name) ? " AND personaggio.nome LIKE \"%{$name}%\" " : '';
        $race_search = ($race && Razze::getInstance()->activeRaces()) ? " AND personaggio.razza = {$race}" : '';
        $gender_search = ($gender) ? " AND personaggio.sesso = {$gender}" : '';
        $order_by_search = match ($order_by) {
            default => 'personaggio.nome',
            'race' => 'razze.nome',
            'gender' => 'sessi.nome',
        };
        $order_for_search = match ($order_for) {
            "DESC" => "DESC",
            default => 'ASC'
        };

        $pgs_list = DB::queryStmt(
            "SELECT personaggio.*,
                    sessi.nome AS sesso_nome, 
                    sessi.immagine AS sesso_icon, 
                    razze.nome AS razza_nome,
                    razze.icon AS razza_icon
                    FROM personaggio
                    LEFT JOIN razze ON personaggio.razza = razze.id
                    LEFT JOIN sessi ON personaggio.sesso = sessi.id
                    WHERE 1 = 1
                    {$name_search}
                    {$race_search}
                    {$gender_search}
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
                "img" => Filters::int($pg_data['url_img_chat']),
                "name" => Filters::out($pg_data['nome']),
                'gender' => Filters::out($pg_data['sesso_nome']),
                'gender_icon' => Filters::out($pg_data['sesso_icon']),
                'race' => Filters::out($pg_data['razza_nome']),
                'race_icon' => Filters::out($pg_data['razza_icon']),
            ];
        }

        $cells = [
            'Immagine chat',
            'Nome',
            'Razza',
            'Sesso',
        ];

        return Template::getInstance()->startTemplate()->renderTable('servizi/anagrafe/results', [
            'body_rows' => $pgs,
            'cells' => $cells,
            'links' => [],
        ]);
    }

}