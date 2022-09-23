<?php

/**
 * @class Gruppi
 * @note Classe che gestisce i gruppi
 */
class Gruppi extends BaseClass
{

    protected bool
        $groups_active,
        $group_icon_chat,
        $group_icon_present,
        $pay_from_money;

    protected function __construct()
    {
        parent::__construct();
        $this->groups_active = Functions::get_constant('GROUPS_ACTIVE');
        $this->pay_from_money = Functions::get_constant('GROUPS_PAY_FROM_MONEY');
        $this->group_icon_chat = Functions::get_constant('GROUPS_ICON_CHAT');
        $this->group_icon_present = Functions::get_constant('GROUPS_ICON_PRESENT');
    }

    /**** CONFIG ****/

    /**
     * @fn activeGroups
     * @note Controlla se i gruppi sono attivi
     * @return bool
     */
    public function activeGroups(): bool
    {
        return $this->groups_active;
    }

    /**
     * @fn payFromMoney
     * @note Controlla se i gruppi pagano dai propri soldi
     * @return bool
     */
    public function payFromMoney(): bool
    {
        return $this->pay_from_money;
    }

    /**
     * @fn activeGroupIconChat
     * @note Controlla se sono attive le icone gruppo in chat
     * @return bool
     */
    public function activeGroupIconChat(): bool
    {
        return $this->group_icon_chat;
    }

    /**
     * @fn activeGroupIconPresent
     * @note Controlla se sono attive le icone gruppo nei presenti
     * @return bool
     */
    public function activeGroupIconPresent(): bool
    {
        return $this->group_icon_present;
    }

    /**** PERMESSI ****/

    /**
     * @fn permissionManageGroups
     * @note Controlla se si hanno i permessi per gestire i gruppi
     * @return bool
     */
    public function permissionManageGroups(): bool
    {
        return Permissions::permission('MANAGE_GROUPS');
    }

    /**
     * @fn haveGroupPower
     * @note Controlla se il personaggio ha almeno un ruolo con poteri o se ha i poteri per un singolo gruppo
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function haveGroupPower(int $id = 0): int
    {

        if($this->permissionManageGroups()){
            return true;
        }

        $extra_query = '';

        if ( $id ) {
            $extra_query = "AND gruppi_ruoli.gruppo = '{$id}'";
        }

        $sql = DB::queryStmt(
            "SELECT COUNT(personaggio_ruolo.id) AS 'TOT' FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id AND personaggio_ruolo.personaggio =:pg
                    WHERE gruppi_ruoli.poteri = 1 AND personaggio_ruolo.id IS NOT NULL {$extra_query}",
            ['pg' => $this->me_id]
        );

        return Filters::int($sql['TOT']);
    }

    /**
     * @fn haveGroupRole
     * @note Controlla se il personaggio ha almeno un ruolo di un singolo gruppo
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function haveGroupRole(int $id): int
    {
        $sql = DB::queryStmt(
            "SELECT COUNT(personaggio_ruolo.id) AS 'TOT' FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id AND personaggio_ruolo.personaggio =:pg
                    WHERE personaggio_ruolo.id IS NOT NULL AND gruppi_ruoli.gruppo = '{$id}'",
            ['pg' => $this->me_id]
        );

        return Filters::int($sql['TOT']);
    }

    /**
     * @fn permissionServiceGroups
     * @note Controllo se ho il permesso per accedere alla pagina dei servizi dei gruppi
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionServiceGroups(int $id = 0): bool
    {
        return $this->activeGroups() && ($this->haveGroupPower($id) || $this->permissionManageGroups());
    }

    /**** TABLE HELPERS ****/

    /**
     * @fn getGroup
     * @note Estrae un gruppo
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getGroup(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllGroups
     * @note Estrae tutti i gruppi
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroups(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi WHERE 1 ", []);
    }

    /**
     * @fn getAllGroupsByType
     * @note Estrae tutti i gruppi di un tipo
     * @param string $type
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroupsByType(string $type, string $val = 'gruppi.*,gruppi_tipo.nome AS tipo_name'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi LEFT JOIN gruppi_tipo ON gruppi_tipo.id = gruppi.tipo WHERE tipo=:tipo ", ['tipo' => $type]);
    }

    /**
     * @fn getGroupPeopleNumber
     * @note Estrae il numero di membri di un gruppo
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function getGroupPeopleNumber(int $id): int
    {
        $sql = DB::queryStmt(
            "SELECT COUNT(personaggio.id) AS 'TOT' FROM personaggio 
                   LEFT JOIN personaggio_ruolo ON personaggio.id = personaggio_ruolo.personaggio
                    LEFT JOIN gruppi_ruoli ON gruppi_ruoli.id = personaggio_ruolo.ruolo
                    WHERE gruppi_ruoli.gruppo = :id AND personaggio_ruolo.id IS NOT NULL",
            ['id' => $id]
        );

        return Filters::int($sql['TOT']);
    }

    /**
     * @fn getAvailableGroups
     * @note Estrae i gruppi che un personaggio puo' gestire
     * @return array
     * @throws Throwable
     */
    public function getAvailableGroups(): array
    {
        $groups = DB::queryStmt("
                    SELECT gruppi_ruoli.gruppo FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id AND personaggio_ruolo.personaggio = :pg
                    WHERE gruppi_ruoli.poteri = 1 AND personaggio_ruolo.id IS NOT NULL", ['pg' => $this->me_id]);

        $groups_list = [];

        foreach ( $groups as $group ) {
            $groups_list[] = $group['gruppo'];
        }

        return $groups_list;
    }

    /**
     * @fn getAllGroupsByIds
     * @note Estrae tutti i gruppi da piu' id
     * @param array $ids
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroupsByIds(array $ids, string $val = '*'): DBQueryInterface
    {
        $toSearch = implode(',', $ids);
        return DB::queryStmt("SELECT {$val} FROM gruppi WHERE id IN ({$toSearch})", []);
    }


    /**** LISTE ****/

    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     * @throws Throwable
     */
    public function listGroups(): string
    {
        $groups = $this->getAllGroups();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $groups);
    }

    /**
     * @fn listAvailableGroups
     * @note Genera gli option per i gruppi disponibili
     * @return string
     * @throws Throwable
     */
    public function listAvailableGroups(): string
    {
        if ( $this->permissionManageGroups() ) {
            $roles = $this->getAllGroups();
        } else {
            $groups = $this->getAvailableGroups();
            $roles = $this->getAllGroupsByIds($groups);
        }
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }


    /** AJAX */

    /**
     * @fn ajaxGroupData
     * @note Estrazione dinamica dei dati di un gruppo
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxGroupData(array $post): DBQueryInterface
    {
        $id = Filters::int($post['id']);
        return $this->getGroup($id)->getData()[0];
    }

    /**** LOADER ****/

    /**
     * @fn loadServicePage
     * @note Index della pagina servizi dei gruppi
     * @param string $op
     * @return string
     */
    public function loadServicePage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            'storage' => 'storage.php',
            'read' => 'read.php',
            default => 'view.php',
        };
    }

    /**
     * @fn loadGroupAdministrationPage
     * @note Index della pagina amministrazione dei gruppi
     * @param string $op
     * @return string
     */
    public function loadGroupAdministrationPage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            'view_extra_earn' => 'view_extra_earn.php',
            default => 'view.php',
        };

    }

    /**** RENDER ****/

    /**
     * @fn groupsList
     * @note Render html della lista dei gruppi
     * @return string
     * @throws Throwable
     */
    public function groupsList(): string
    {
        $types = GruppiTipi::getInstance()->getAllTypes();
        $list = [];

        foreach ( $types as $type ) {
            $typeId = Filters::int($type['id']);
            $groups = $this->getAllGroupsByType($typeId);
            foreach ( $groups as $group ) {
                $groupId = Filters::int($group['id']);
                $group['member_number'] = $this->getGroupPeopleNumber($groupId);
                $list[] = $group;
            }
        }

        return Template::getInstance()->startTemplate()->renderTable(
            'servizi/gruppi_list',
            $this->renderGroupsList($list)
        );
    }

    /**
     * @fn renderGroupsList
     * @note Render html lista gruppi
     * @param array $list
     * @return array
     * @throws Throwable
     */
    public function renderGroupsList(array $list): array
    {
        $row_data = [];

        foreach ( $list as $row ) {

            $array = [
                'id' => Filters::int($row['id']),
                'name' => Filters::out($row['nome']),
                'member_number' => Filters::int($row['member_number']),
                'tipo' => Filters::out($row['tipo_name']),
                'storage_permission' => GruppiOggetto::getInstance()->permissionViewStorage($row['id']),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
            'Numero Membri',
            'Tipo',
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

    /** GESTIONE */

    /**
     * @fn NewGroup
     * @note Inserisce un gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewGroup(array $post): array
    {
        if ( $this->permissionManageGroups() ) {

            $nome = Filters::in($post['nome']);
            $tipo = Filters::int($post['tipo']);
            $img = Filters::in($post['immagine']);
            $url = Filters::in($post['url']);
            $statuto = Filters::in($post['statuto']);
            $denaro = Filters::int($post['denaro']);
            $visibile = Filters::checkbox($post['visibile']);

            DB::queryStmt(
                "INSERT INTO gruppi (nome,tipo,immagine,url,statuto,denaro,visibile )  
                        VALUES (:nome,:tipo,:img,:url,:statuto,:denaro,:visibile)",
                [
                    'nome' => $nome,
                    'tipo' => $tipo,
                    'img' => $img,
                    'url' => $url,
                    'statuto' => $statuto,
                    'denaro' => $denaro,
                    'visibile' => $visibile,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo creato.',
                'swal_type' => 'success',
                'groups_list' => $this->listGroups(),
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
     * @fn ModGroup
     * @note Aggiorna un gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModGroup(array $post): array
    {
        if ( $this->permissionManageGroups() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $tipo = Filters::int($post['tipo']);
            $img = Filters::in($post['immagine']);
            $url = Filters::in($post['url']);
            $statuto = Filters::in($post['statuto']);
            $denaro = Filters::int($post['denaro']);
            $visibile = Filters::checkbox($post['visibile']);

            DB::queryStmt(
                "UPDATE  gruppi 
                SET nome = :nome,tipo=:tipo, immagine=:img,url=:url,statuto=:statuto,denaro=:denaro,visibile=:visibile WHERE id=:id",
                [
                    'id' => $id,
                    'nome' => $nome,
                    'tipo' => $tipo,
                    'img' => $img,
                    'url' => $url,
                    'statuto' => $statuto,
                    'denaro' => $denaro,
                    'visibile' => $visibile,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo modificato.',
                'swal_type' => 'success',
                'groups_list' => $this->listGroups(),
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
     * @fn DelGroup
     * @note Cancella un gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelGroup(array $post): array
    {
        if ( $this->permissionManageGroups() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM gruppi WHERE id=:id", ['id' => $id]);
            DB::queryStmt("DELETE FROM gruppi_ruoli WHERE gruppo=:id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo eliminato.',
                'swal_type' => 'success',
                'groups_list' => $this->listGroups(),
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

    /*** CRONJOB ***/

    /**
     * @fn cronSalaries
     * @note Cronjob di assegnazione dei salari giornalieri
     * @return void
     * @throws Throwable
     */
    public function cronSalaries(): void
    {

        $pgs = Personaggio::getInstance()->getAllPg();

        foreach ( $pgs as $pg ) {
            $id = Filters::int($pg['id']);

            //! Tenere divisi i totali di ogni singola categoria, per poter inviare messaggi
            //! e log specifici per tipologia
            $totale_ruoli = 0;
            $totale_lavori = 0;
            $totale_extra = 0;

            // STIPENDI DA RUOLI
            $ruoli = GruppiRuoli::getInstance()->getCharacterRolesSalaries($id);
            foreach ( $ruoli as $ruolo ) {
                $group_id = Filters::int($ruolo['id']);
                $group_name = Filters::out($ruolo['nome']);
                $corp_money = Filters::int($ruolo['denaro']);
                $stipendio = Filters::int($ruolo['stipendio']);

                if ( !Gruppi::getInstance()->payFromMoney() ) {
                    $totale_ruoli += Filters::int($stipendio);
                } else {

                    if ( $corp_money && ($corp_money >= $stipendio) ) {
                        $totale_ruoli += Filters::int($stipendio);
                        DB::queryStmt(
                            "UPDATE gruppi SET denaro=denaro-:stipendio WHERE id=:id LIMIT 1",
                            [
                                'id' => $group_id,
                                'stipendio' => $stipendio,
                            ]
                        );
                    } else {
                        $titolo = Filters::in('Stipendio non depositato');
                        $testo = Filters::in("Il gruppo '{$group_name}' non ha abbastanza soldi per pagare il tuo stipendio di {$stipendio} dollari.");
                        DB::queryStmt(
                            "INSERT INTO messaggi(mittente,destinatario,tipo,oggetto,testo) VALUES('System',:id,0,:titolo,:testo) ",
                            [
                                'id' => $id,
                                'titolo' => $titolo,
                                'testo' => $testo,
                            ]
                        );
                    }

                }
            }

            // STIPENDI DA LAVORI
            $lavori = GruppiLavori::getInstance()->getCharacterWorksSalaries($id);
            foreach ( $lavori as $lavoro ) {
                $totale_lavori += Filters::int($lavoro['stipendio']);
            }

            //STIPENDI EXTRA
            if ( GruppiStipendiExtra::getInstance()->activeExtraEarn() ) {
                $extra = GruppiStipendiExtra::getInstance()->getPgExtraEarns($id, 'gruppi_stipendi_extra.*,gruppi.denaro,gruppi.nome AS group_name');
                foreach ( $extra as $extra_earn ) {
                    $extra_id = Filters::int($extra_earn['id']);
                    $last_exec = Filters::out($extra_earn['last_exec']);
                    $interval = Filters::int($extra_earn['interval']);
                    $interval_type = Filters::out($extra_earn['interval_type']);
                    $extra_val = Filters::int($extra_earn['valore']);
                    $group_id = Filters::int($extra_earn['gruppo']);
                    $corp_money = Filters::int($extra_earn['denaro']);
                    $group_name = Filters::out($extra_earn['group_name']);

                    if ( CarbonWrapper::needExec($interval, $interval_type, $last_exec) ) {

                        if ( !Gruppi::getInstance()->payFromMoney() ) {
                            $totale_extra += Filters::int($extra_val);
                        } else {
                            if ( $corp_money && ($corp_money >= $extra_val) ) {
                                $totale_extra += Filters::int($extra_val);
                                DB::queryStmt(
                                    "UPDATE gruppi SET denaro=denaro-:extra WHERE id=:id LIMIT 1",
                                    [
                                        'id' => $group_id,
                                        'extra' => $extra_val,
                                    ]
                                );
                            } else {
                                $titolo = Filters::in('Stipendio non depositato');
                                $testo = Filters::in("Il gruppo '{$group_name}' non ha abbastanza soldi per pagare il tuo stipendio extra di {$extra_val} dollari.");
                                DB::queryStmt(
                                    "INSERT INTO messaggi(mittente,destinatario,tipo,oggetto,testo) VALUES('System',:id,0,:titolo,:testo) ",
                                    [
                                        'id' => $id,
                                        'titolo' => $titolo,
                                        'testo' => $testo,
                                    ]
                                );
                            }
                        }

                        DB::queryStmt(
                            "UPDATE gruppi_stipendi_extra SET last_exec=NOW() WHERE id=:id LIMIT 1",
                            ['id' => $extra_id]
                        );
                    }
                }
            }

            // TOTALE COMPLESSIVO
            $totale = Filters::int($totale_lavori + $totale_ruoli + $totale_extra);

            // ASSEGNAZIONE DEL DENARO AL PG
            Personaggio::updatePgData($id, "banca=banca+:bank", ['bank' => $totale]);
        }
    }
}