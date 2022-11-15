<?php

class SchedaDiario extends Scheda
{

    private bool $diary_active;

    /**
     * @fn __construct
     * @note Class constructor
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();

        $this->diary_active = Functions::get_constant('DIARY_ENABLED');
    }

    /*** TABLE HELPERS ***/

    /**
     * @fn getDiary
     * @note Restituisce una pagina di diario
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getDiary(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM diario WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllDiaryByCharacter
     * @note Restituisce tutti i diari di un personaggio
     * @param int $pg
     * @param bool $private_too
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllDiaryByCharacter(int $pg, bool $private_too = false, string $val = '*'): DBQueryInterface
    {
        $extra_query = (!$private_too) ? "AND visibile = '1'" : '';
        return DB::queryStmt("SELECT {$val} FROM diario WHERE personaggio = :pg {$extra_query} ORDER BY `data` DESC", ['pg' => $pg]);
    }

    /***** CONFIG ****/

    /**
     * @fn diaryActive
     * @note Restituisce se il diario è attivo
     * @return bool
     */
    public function diaryActive(): bool
    {
        return $this->diary_active;
    }


    /**** PERMISSION ***/

    /**
     * @fn permessiViewPrivateDiary
     * @note Permessi per visualizzare la scheda diario privata
     * @param int $id_pg
     * @return bool
     * @throws Throwable
     */
    public function permessiViewPrivateDiary(int $id_pg): bool
    {
        return (Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_DIARY_VIEW'));
    }

    /**
     * @fn permessiUpdatePrivateDiary
     * @note Permessi per visualizzare la scheda diario privata
     * @param int $id_pg
     * @return bool
     * @throws Throwable
     */
    public function permessiUpdateDiary(int $id_pg): bool
    {
        return (Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_DIARY_EDIT'));
    }

    /*** FUNCTIONS ***/

    /**
     * @fn newDiary
     * @note Crea una nuova pagina diario
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newDiary(array $post): array
    {

        $pg = Filters::int($post['pg']);

        if ( $this->permessiUpdateDiary($pg) ) {

            $titolo = Filters::in($post['titolo']);
            $date = Filters::in($post['data']);
            $visibile = Filters::checkbox($post['visibile']);
            $testo = Filters::in($post['testo']);

            DB::queryStmt(
                "INSERT INTO diario (`personaggio`, `titolo`, `data`, `visibile`, `testo`)  
                        VALUES (:pg, :titolo, :data, :visibile, :testo)",
                [
                    'pg' => $pg,
                    'titolo' => $titolo,
                    'data' => $date,
                    'visibile' => $visibile,
                    'testo' => $testo,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota aggiunta correttamente.',
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
     * @fn editDiary
     * @note Modifica una pagina diario
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editDiary(array $post): array
    {

        $id = Filters::int($post['id']);
        $diary_data = $this->getDiary($id);
        $owner = Filters::int($diary_data['personaggio']);

        if ( $this->permessiUpdateDiary($owner) ) {

            $titolo = Filters::in($post['titolo']);
            $date = Filters::in($post['data']);
            $visibile = Filters::checkbox($post['visibile']);
            $testo = Filters::in($post['testo']);

            DB::queryStmt(
                "UPDATE diario 
                      SET `titolo` = :titolo, `data` = :data, `visibile` = :visibile, `testo` = :testo, `data_modifica` = NOW()
                      WHERE id = :id",
                [
                    'id' => $id,
                    'titolo' => $titolo,
                    'data' => $date,
                    'visibile' => $visibile,
                    'testo' => $testo,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota modifica correttamente.',
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
     * @fn deleteDiary
     * @note Elimina una pagina diario
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteDiary(array $post): array
    {
        $id = Filters::int($post['id']);
        $diary_data = $this->getDiary($id, 'personaggio');
        $owner = Filters::int($diary_data['personaggio']);

        if ( $this->permessiUpdateDiary($owner) ) {
            DB::queryStmt(
                "DELETE FROM diario WHERE id=:id LIMIT 1",
                [
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota eliminata correttamente.',
                'swal_type' => 'success',
                'new_template' => $this->diaryList($owner),
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


    /***** RENDER ***/

    /**
     * @fn renderDiaryPage
     * @note Elabora i dati della pagina del diario
     * @param int $id_pg
     * @return array
     * @throws Throwable
     */
    public function renderDiaryPage(int $id_pg): array
    {
        $private = false;

        if ( $this->permessiViewPrivateDiary($id_pg) ) {
            $private = true;
        }

        $data = $this->getAllDiaryByCharacter($id_pg, $private);

        $cells = [
            'Data',
            'Titolo',
            'Visibile',
            'Comandi',
        ];

        $diary_data = [];

        foreach ( $data as $diary ) {
            $diary_data[] = [
                "id_pg" => $id_pg,
                "id" => Filters::int($diary['id']),
                "date" => Filters::date($diary['data'], 'd/m/Y'),
                "visibile" => Filters::bool($diary['visibile']) ? 'Si' : 'No',
                "titolo" => Filters::out($diary['titolo']),
                "update_permission" => $this->permessiUpdateDiary($id_pg),
            ];
        }

        return [
            'body_rows' => $diary_data,
            'cells' => $cells,
            'table_title' => 'Diario',
        ];

    }

    /**
     * @fn diaryList
     * @note Renderizza la lista del diario
     * @param int $id_pg
     * @return string
     * @throws Throwable
     */
    public function diaryList(int $id_pg): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/diario/list',
            $this->renderDiaryPage($id_pg)
        );
    }

    /**
     * @fn renderDiaryPage
     * @note Elabora i dati della pagina del diario
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public function renderDiaryView(int $id): array
    {
        $data = $this->getDiary($id);

        $diary_data = [
            "testo" => Filters::html($data['testo']),
        ];

        $titolo = Filters::out($data['titolo']);
        $date = Filters::date($data['data'], 'd/m/Y');
        $title = "{$titolo} - {$date}";

        $insert = Filters::date($data['data_inserimento'], 'd/m/Y');
        $edit = ($data['data_modifica']) ? Filters::date($data['data_modifica'], 'd/m/Y') : '';
        $footer_text = " Data inserimento: {$insert} - Data modifica: {$edit}";

        return [
            'data' => $diary_data,
            'table_title' => $title,
            "footer_text" => $footer_text,
        ];

    }

    /**
     * @fn diaryView
     * @note Renderizza la pagina del diario
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function diaryView(int $id): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/diario/page',
            $this->renderDiaryView($id)
        );
    }

}