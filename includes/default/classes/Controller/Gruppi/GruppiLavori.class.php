<?php

/**
 * @class GruppiTipi
 * @note Classe che gestisce i tipi di gruppo
 */
class GruppiLavori extends Gruppi
{

    private int
        $max_works,
        $dimissions_days;

    private bool
        $active_works;

    /**
     * @fn __construct
     * @note Costruttore della classe
     */
    protected function __construct()
    {
        parent::__construct();
        $this->active_works = Functions::get_constant('WORKS_ACTIVE');
        $this->max_works = Functions::get_constant('WORKS_MAX');
        $this->dimissions_days = Functions::get_constant('WORKS_DIMISSIONS_DAYS');
    }

    /*** CONFIG ***/

    /**
     * @fn activeWorks
     * @note Ritorna se i lavori sono attivi
     * @return bool
     */
    public function activeWorks(): bool
    {
        return $this->active_works;
    }

    /**** PERMESSI ****/

    /**
     * @fn permissionManageWorks
     * @note Controlla permessi sulla gestione dei lavori
     * @return bool
     */
    public function permissionManageWorks(): bool
    {
        return Permissions::permission('MANAGE_WORKS');
    }

    /**** TABLE HELPERS ****/

    /**
     * @fn getWork
     * @note Estrae un lavoro preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getWork(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT $val FROM gruppi_lavori WHERE id = :id", ['id' => $id]);
    }

    /**
     * @fn getAllWorks
     * @note Estrae tutti i lavori
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllWorks(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM lavori WHERE 1", []);
    }

    /**
     * @fn getCharacterWorksNumbers
     * @note Conta quanti lavori ha un personaggio
     * @param int $pg
     * @return int
     * @throws Throwable
     */
    public function getCharacterWorksNumbers(int $pg): int
    {

        $groups = DB::queryStmt("
                SELECT COUNT(personaggio_lavoro.id) AS 'TOT' FROM personaggio_lavoro 
                WHERE personaggio_lavoro.personaggio =:pg",
            ['pg' => $pg]
        );

        return Filters::int($groups['TOT']);
    }

    /**
     * @fn getCharacterWork
     * @note Conta quanti lavori ha un personaggio
     * @param int $pg
     * @param int $work
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCharacterWork(int $pg, int $work): DBQueryInterface
    {

        return DB::queryStmt("
                SELECT * FROM personaggio_lavoro 
                WHERE personaggio_lavoro.personaggio =:pg AND personaggio_lavoro.lavoro=:work",
            ['pg' => $pg, 'work' => $work]
        );
    }

    /**
     * @fn getCharacterSalaries
     * @note Ottiene tutti gli stipendi dei ruoli di un personaggio
     * @param int $pg
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCharacterWorksSalaries(int $pg): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT lavori.stipendio FROM personaggio_lavoro 
                    LEFT JOIN lavori ON (personaggio_lavoro.lavoro = lavori.id)
                    WHERE personaggio_lavoro.personaggio =:pg",
            ['pg' => $pg]
        );
    }

    /**** LISTE ****/

    /**
     * @fn listWorks
     * @note Genera gli option per i lavori
     * @return string
     * @throws Throwable
     */
    public function listWorks(): string
    {
        $works = $this->getAllWorks();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $works);
    }

    /** RENDER */

    /**
     * @fn groupsList
     * @note Render html della lista dei gruppi
     * @return string
     * @throws Throwable
     */
    public function worksList(): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'servizi/works_list',
            $this->renderWorksList($this->getAllWorks())
        );
    }

    /**
     * @fn renderGroupsList
     * @note Render html lista gruppi
     * @param object $list
     * @return array
     * @throws Throwable
     */
    public function renderWorksList(object $list): array
    {
        $row_data = [];

        foreach ( $list as $row ) {

            $array = [
                'id' => Filters::int($row['id']),
                'name' => Filters::out($row['nome']),
                'stipendio' => Filters::int($row['stipendio']),
                'immagine' => Filters::out($row['immagine']),
                'buyable' => ($this->getCharacterWorksNumbers($this->me_id) < $this->max_works) && empty($this->getCharacterWork($this->me_id, $row['id'])),
                'dimissions' => !empty($this->getCharacterWork($this->me_id, $row['id'])),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
            'Logo',
            'Stipendio',
            'Comandi',
        ];
        $links = [
            ['href' => "/main.php?page=uffici", 'text' => 'Indietro'],
        ];
        return [
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
        ];
    }

    /**** AJAX ****/

    /**
     * @fn ajaxWorkData
     * @note Estrae i dati di un lavoro dinamicamente
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxWorkData(array $post): DBQueryInterface
    {
        $id = Filters::int($post['id']);
        return $this->getWork($id);
    }

    /**** GESTIONE ****/

    /**
     * @fn NewWork
     * @note Inserisce un lavoro
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewWork(array $post): array
    {
        if ( $this->permissionManageWorks() ) {

            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);

            DB::queryStmt("INSERT INTO lavori (nome, descrizione, immagine, stipendio) VALUES (:nome, :descr, :img, :stipendio)",
                [
                    'nome' => $nome,
                    'descr' => $descr,
                    'img' => $img,
                    'stipendio' => $stipendio,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Lavoro creato.',
                'swal_type' => 'success',
                'works_list' => $this->listWorks(),
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
     * @fn ModWork
     * @note Aggiorna un lavoro
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModWork(array $post): array
    {
        if ( $this->permissionManageWorks() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);

            DB::queryStmt("UPDATE lavori SET nome=:nome, descrizione=:descr, immagine=:img, stipendio=:stipendio WHERE id=:id",
                [
                    'id' => $id,
                    'nome' => $nome,
                    'descr' => $descr,
                    'img' => $img,
                    'stipendio' => $stipendio,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Lavoro modificato.',
                'swal_type' => 'success',
                'works_list' => $this->listWorks(),
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
     * @fn DelWork
     * @note Cancella un lavoro
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelWork(array $post): array
    {
        if ( $this->permissionManageWorks() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM lavori WHERE id=:id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Lavoro eliminato.',
                'swal_type' => 'success',
                'works_list' => $this->listWorks(),
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
     * @fn assignWork
     * @note  Assegnazione lato gestione dei lavori
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function assignWork(array $post): array
    {

        if ( $this->activeWorks() && $this->permissionManageWorks() ) {

            $id = Filters::int($post['lavoro']);
            $pg = Filters::int($post['personaggio']);

            if ( $this->getCharacterWorksNumbers($pg) < $this->max_works ) {

                if ( empty($this->getCharacterWork($pg, $id)) ) {

                    $scadenza = CarbonWrapper::AddDays(date("Y-m-d"), $this->dimissions_days);

                    DB::queryStmt("INSERT INTO lavori_personaggi (personaggio, lavoro, scadenza) VALUES (:pg, :lavoro, :scadenza)",
                        [
                            'pg' => $pg,
                            'lavoro' => $id,
                            'scadenza' => $scadenza,
                        ]
                    );

                    return [
                        'response' => true,
                        'swal_title' => 'Operazione riuscita!',
                        'swal_message' => 'Lavoro assegnato.',
                        'swal_type' => 'success',
                        'works_table' => $this->worksList(),
                    ];
                } else {
                    return [
                        'response' => false,
                        'swal_title' => 'Operazione fallita!',
                        'swal_message' => 'Lavoro gia preso.',
                        'swal_type' => 'error',
                    ];
                }
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Raggiunto numero massimo di lavori.',
                    'swal_type' => 'error',
                ];
            }

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
     * @fn autoRemoveWork
     * @note Rimozione lato player dei lavori
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function removeWork(array $post): array
    {

        if ( $this->activeWorks() && $this->permissionManageWorks() ) {

            $id = Filters::int($post['lavoro']);
            $pg = Filters::int($post['personaggio']);
            $work_data = $this->getCharacterWork($pg, $id);

            if ( !empty($work_data) ) {
                DB::queryStmt("DELETE FROM lavori_personaggi WHERE personaggio=:pg AND lavoro=:lavoro",
                    [
                        'pg' => $pg,
                        'lavoro' => $id,
                    ]
                );

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Lavoro rimosso.',
                    'swal_type' => 'success',
                    'works_table' => $this->worksList(),

                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Lavoro mai preso.',
                    'swal_type' => 'error',
                ];
            }

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**** SERVIZI ****/

    /**
     * @fn autoAssignWork
     * @note Auto Assegnazione lato player dei lavori
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function autoAssignWork(array $post): array
    {

        if ( $this->activeWorks() ) {

            $id = Filters::int($post['id']);

            if ( $this->getCharacterWorksNumbers($this->me_id) < $this->max_works ) {

                if ( empty($this->getCharacterWork($this->me_id, $id)) ) {

                    $scadenza = CarbonWrapper::AddDays(date("Y-m-d"), $this->dimissions_days);

                    DB::queryStmt("INSERT INTO lavori_personaggi (personaggio, lavoro, scadenza) VALUES (:pg, :lavoro, :scadenza)",
                        [
                            'pg' => $this->me_id,
                            'lavoro' => $id,
                            'scadenza' => $scadenza,
                        ]
                    );

                    return [
                        'response' => true,
                        'swal_title' => 'Operazione riuscita!',
                        'swal_message' => 'Lavoro assegnato.',
                        'swal_type' => 'success',
                        'works_table' => $this->worksList(),
                    ];
                } else {
                    return [
                        'response' => false,
                        'swal_title' => 'Operazione fallita!',
                        'swal_message' => 'Lavoro gia preso.',
                        'swal_type' => 'error',
                    ];
                }
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Raggiunto numero massimo di lavori.',
                    'swal_type' => 'error',
                ];
            }

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
     * @fn autoRemoveWork
     * @note Auto Rimozione lato player dei lavori
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function autoRemoveWork(array $post): array
    {

        if ( $this->activeWorks() ) {

            $id = Filters::int($post['id']);
            $work_data = $this->getCharacterWork($this->me_id, $id);

            if ( !empty($work_data) ) {

                $scadenza = Filters::date($work_data['scadenza'], 'Y-m-d');
                $scadenza_visual = Filters::date($work_data['scadenza'], 'd/m/Y');

                $diff = CarbonWrapper::DatesDifferenceDays(date('Y-m-d'), $scadenza);

                if ( $diff > $this->dimissions_days ) {

                    DB::queryStmt("DELETE FROM lavori_personaggi WHERE personaggio=:pg AND lavoro=:lavoro LIMIT 1",
                        [
                            'pg' => $this->me_id,
                            'lavoro' => $id,
                        ]
                    );

                    return [
                        'response' => true,
                        'swal_title' => 'Operazione riuscita!',
                        'swal_message' => 'Lavoro rimosso.',
                        'swal_type' => 'success',
                        'works_table' => $this->worksList(),

                    ];
                } else {
                    return [
                        'response' => false,
                        'swal_title' => 'Operazione fallita!',
                        'swal_message' => "Potrai licenziarti solo dopo il {$scadenza_visual}.",
                        'swal_type' => 'error',
                    ];
                }
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Lavoro mai preso.',
                    'swal_type' => 'error',
                ];
            }

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