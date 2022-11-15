<?php

class PersonaggioAbilita extends Personaggio
{

    protected function __construct()
    {
        parent::__construct();
    }

    /**** TABLES HELPERS ***/

    /**
     * @fn getAllPgAbility
     * @note Ottiene tutte le abilita' di un personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllPgAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_abilita
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio=:pg",
            [
                'pg' => $pg,
            ]
        );
    }

    /**
     * @fn getPgAbility
     * @note Ottiene la singola abilita' del personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPgAbility(int $id, int $pg, string $val = 'abilita.*,personaggio_abilita.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio=:pg AND abilita =:id LIMIT 1",
            [
                'pg' => $pg,
                'id' => $id,
            ]
        );
    }

    /**
     * @fn getPgGenericAbility
     * @note Ottiene le generiche abilita' del personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPgGenericAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio=:pg AND abilita.razza = '-1'", ['pg' => $pg]);
    }

    /**
     * @fn getPgRaceAbility
     * @note Ottiene le abilita' razziali del personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPgRaceAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*'): DBQueryInterface
    {
        # Estraggo la razza del pg
        $pg_data = Personaggio::getPgData($this->me_id, 'razza');
        $race = Filters::int($pg_data['razza']);

        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio=:pg AND abilita.razza = :race",
            [
                'pg' => $pg,
                'race' => $race,
            ]
        );
    }

    /*** PERMISSION */

    /**
     * @fn upgradeSkillPermission
     * @note Controlla i permessi per upgrade delle skill
     * @param string $pg
     * @param int $grado
     * @return bool
     * @throws Throwable
     */
    public function permissionUpgradeAbilita(string $pg, int $grado): bool
    {
        $pg = Filters::int($pg);
        $grado = Filters::int($grado);
        $new_grado = Filters::int(($grado + 1));

        return (((Personaggio::isMyPg($pg)) || Permissions::permission('UPGRADE_SCHEDA_ABI')) && ($new_grado <= Abilita::getInstance()->abiLevelCap()));
    }

    /**
     * @fn downgradeSkillPermission
     * @note Controlla se si hanno i permessi per diminuire una skill
     * @param int $grado
     * @return bool
     * @throws Throwable
     */
    public function permissionDowngradeAbilita(int $grado): bool
    {
        $grado = Filters::int($grado);
        return (Permissions::permission('DOWNGRADE_SCHEDA_ABI') && ($grado > 0));
    }

    /*** FUNCTIONS */

    /**
     * @fn RemainedExp
     * @note Esperienza Rimasta al pg
     * @param int $pg
     * @return float
     * @throws Throwable
     */
    public function RemainedExp(int $pg): float
    {

        # Estraggo abilita' ed esperienza pg
        $abi_pg = $this->getAllPgAbility($pg);
        $pg_data = Personaggio::getPgData($pg, 'esperienza');
        $exp_pg = Filters::int($pg_data['esperienza']);

        # Inizio le variabili necessarie
        $px_spesi = 0;
        $count = 1;

        # Per ogni abilita del pg
        foreach ( $abi_pg as $row_pg ) {

            # Estraggo i dati dell'abilita
            $grado = Filters::int($row_pg['grado']);
            $abi_id = Filters::int($row_pg['id_abilita']);

            # Per ogni livello dell'abilita
            while ( $count <= $grado ) {

                # Estraggo il costo
                $extra = AbilitaExtra::getInstance()->getAbilitaExtra($abi_id, $count, 'costo');

                # Se non c'e' un costo, calcolo quello di default per quel livello
                if ( Abilita::getInstance()->extraActive() && !empty($extra['costo']) && ($extra['costo'] > 0) ) {
                    $px_abi = Filters::int($extra['costo']);
                } else {
                    $px_abi = Abilita::getInstance()->defaultCalcUp($count);
                }

                # Aggiungo il costo calcolato
                $px_spesi += $px_abi;
                $count++;
            }

            # Riavvio il counter per la prossima abilita
            $count = 1;
        }

        # Ritorno la differenza tra l'esperienza del pg e quella spesa
        return round($exp_pg - $px_spesi);
    }

    /**
     * @fn upgradeAbilita
     * @note Aumenta una skill del personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function upgradeAbilita(array $post): array
    {
        $abi = Filters::int($post['abilita']);
        $pg = Filters::int($post['pg']);
        $abi_pg = $this->getPgAbility($abi, $pg, 'personaggio_abilita.grado');
        $grado = Filters::int($abi_pg['grado']);

        if ( $this->permissionUpgradeAbilita($pg, $grado) ) {

            $new_grado = Filters::int(($grado + 1));

            if ( AbilitaRequisiti::getInstance()->requirementControl($pg, $abi, $new_grado) ) {
                $exp_remained = $this->RemainedExp($pg);

                if ( Abilita::getInstance()->extraActive() ) {

                    $extra = AbilitaExtra::getInstance()->getAbilitaExtra($abi, $new_grado, 'costo');

                    if ( !empty($extra['costo']) && ($extra['costo'] > 0) ) {
                        $costo = Filters::int($extra['costo']);
                    } else {
                        $costo = Abilita::getInstance()->defaultCalcUp($new_grado);
                    }

                } else {
                    $costo = Abilita::getInstance()->defaultCalcUp($new_grado);
                }

                if ( $exp_remained >= $costo ) {

                    if ( $grado == 0 ) {
                        DB::queryStmt(
                            "INSERT INTO personaggio_abilita(personaggio,abilita,grado) VALUES(:pg,:abi,:grado)",
                            [
                                'pg' => $pg,
                                'abi' => $abi,
                                'grado' => $new_grado,
                            ]
                        );
                    } else {
                        DB::queryStmt(
                            "UPDATE personaggio_abilita SET grado=:grado WHERE personaggio=:id AND abilita=:abi LIMIT 1",
                            [
                                'grado' => $new_grado,
                                'id' => $pg,
                                'abi' => $abi,
                            ]
                        );
                    }

                    return [
                        'response' => true,
                        'swal_title' => 'Operazione riuscita!',
                        'swal_message' => 'Abilità aumentata con successo.',
                        'swal_type' => 'success',
                        'new_template' => SchedaAbilita::getInstance()->abilityPage($pg),
                        'remained_exp' => PersonaggioAbilita::getInstance()->RemainedExp($pg),
                    ];

                } else {
                    return [
                        'response' => false,
                        'swal_title' => 'Operazione negata!',
                        'swal_message' => 'Non hai abbastanza esperienza per l\'acquisto.',
                        'swal_type' => 'error',
                    ];
                }
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione negata!',
                    'swal_message' => 'Non hai i requisiti necessari per questa abilita.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione negata!',
                'swal_message' => 'Non hai i permessi per effettuare questa operazione.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn downgradeAbilita
     * @note Diminuisce un'abilita' di un livello
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function downgradeAbilita(array $post): array
    {
        $abi = Filters::int($post['abilita']);
        $pg = Filters::int($post['pg']);

        $abi_data = $this->getPgAbility($abi, $pg, 'grado');
        $grado = Filters::int($abi_data['grado']);

        if ( $this->permissionDowngradeAbilita($grado) ) {

            $new_grado = ($grado - 1);

            if ( $new_grado == 0 ) {
                DB::queryStmt(
                    "DELETE FROM personaggio_abilita WHERE personaggio=:pg AND abilita=:abi LIMIT 1",
                    [
                        'pg' => $pg,
                        'abi' => $abi,
                    ]
                );
            } else {
                DB::queryStmt(
                    "UPDATE personaggio_abilita SET grado=:grado WHERE personaggio=:pg AND abilita=:abi LIMIT 1",
                    [
                        'grado' => $new_grado,
                        'pg' => $pg,
                        'abi' => $abi,
                    ]
                );
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Abilità diminuita correttamente.',
                'swal_type' => 'success',
                'new_template' => SchedaAbilita::getInstance()->abilityPage($pg),
                'remained_exp' => PersonaggioAbilita::getInstance()->RemainedExp($pg),

            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione negata!',
                'swal_message' => 'Non hai i permessi per effettuare questa operazione.',
                'swal_type' => 'error',
            ];
        }

    }

}
