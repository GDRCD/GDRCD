<?php

class PersonaggioAbilita extends Personaggio
{
    private $abi_class,
        $req_class,
        $extra_class;

    protected function __construct()
    {
        parent::__construct();

        $this->abi_class = Abilita::getInstance();
        $this->req_class = AbilitaRequisiti::getInstance();
        $this->extra_class = AbilitaExtra::getInstance();
    }

    /**** TABLES HELPERS ***/

    /**
     * @fn getAllPgAbility
     * @note Ottiene tutte le abilita' di un personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPgAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*')
    {
        return DB::query("SELECT {$val} FROM personaggio_abilita
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio='{$pg}'", 'result');
    }

    /**
     * @fn getPgAbility
     * @note Ottiene la singola abilita' del personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPgAbility(int $id, int $pg, string $val = 'abilita.*,personaggio_abilita.*')
    {
        return DB::query("SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio='{$pg}' AND abilita ='{$id}' LIMIT 1");
    }

    /**
     * @fn getPgGenericAbility
     * @note Ottiene le generiche abilita' del personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPgGenericAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*')
    {
        return DB::query("SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio='{$pg}' AND abilita.razza = '-1'", 'result');
    }

    /**
     * @fn getPgRaceAbility
     * @note Ottiene le abilita' razziali del personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPgRaceAbility(int $pg, string $val = 'abilita.*,personaggio_abilita.*')
    {
        # Estaggo la razza del pg
        $pg_data = Personaggio::getPgData($this->me_id, 'id_razza');
        $race = Filters::int($pg_data['id_razza']);

        return DB::query("SELECT {$val} FROM personaggio_abilita 
                                LEFT JOIN abilita ON (personaggio_abilita.abilita = abilita.id)
                                WHERE personaggio_abilita.personaggio='{$pg}' AND abilita.razza = '{$race}'", 'result');
    }

    /*** PERMISSION */

    /**
     * @fn upgradeSkillPermission
     * @note Controlla i permessi per l'upgrade delle skill
     * @param string $pg
     * @param int $grado
     * @return bool
     */
    public function permissionUpgradeAbilita(string $pg, int $grado): bool
    {
        $pg = Filters::int($pg);
        $grado = Filters::int($grado);
        $new_grado = Filters::int(($grado + 1));

        return (((Personaggio::isMyPg($pg)) || Permissions::permission('UPGRADE_SCHEDA_ABI')) && ($new_grado <= $this->abi_class->abiLevelCap()));
    }

    /**
     * @fn downgradeSkillPermission
     * @note Controlla se si hanno i permessi per diminuire una skill
     * @param int $grado
     * @return bool
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
                $extra = $this->extra_class->getAbilitaExtra($abi_id, $count, 'costo');

                # Se non c'e' un costo, calcolo quello di default per quel livello
                if ( $this->abi_class->extraActive() && !empty($extra['costo']) && ($extra['costo'] > 0) ) {
                    $px_abi = Filters::int($extra['costo']);
                } else {
                    $px_abi = $this->abi_class->defaultCalcUp($count);
                }

                # Aggiungo il costo calcolato
                $px_spesi += $px_abi;
                $count++;
            }

            # Restarto il counter per la prossima abilita
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
     */
    public function upgradeAbilita(array $post): array
    {
        $abi = Filters::int($post['abilita']);
        $pg = Filters::int($post['pg']);
        $abi_pg = $this->getPgAbility($abi, $pg, 'personaggio_abilita.grado');
        $grado = Filters::int($abi_pg['grado']);

        if ( $this->permissionUpgradeAbilita($pg, $grado) ) {

            $new_grado = Filters::int(($grado + 1));

            if ( $this->req_class->requirementControl($pg, $abi, $new_grado) ) {
                $exp_remained = $this->RemainedExp($pg);

                if ( $this->abi_class->extraActive() ) {

                    $extra = $this->extra_class->getAbilitaExtra($abi, $new_grado, 'costo');

                    if ( !empty($extra['costo']) && ($extra['costo'] > 0) ) {
                        $costo = Filters::int($extra['costo']);
                    } else {
                        $costo = $this->abi_class->defaultCalcUp($new_grado);
                    }

                } else {
                    $costo = $this->abi_class->defaultCalcUp($new_grado);
                }

                if ( $exp_remained >= $costo ) {

                    if ( $grado == 0 ) {
                        DB::query("INSERT INTO personaggio_abilita(personaggio,abilita,grado) VALUES('{$pg}','{$abi}','{$new_grado}')");
                    } else {
                        DB::query("UPDATE personaggio_abilita SET grado='{$new_grado}' WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
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
                DB::query("DELETE FROM personaggio_abilita WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
            } else {
                DB::query("UPDATE personaggio_abilita SET grado='{$new_grado}' WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
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

    /**
     * @fn operationDone
     * @note Ritorna un messaggio di successo/fallimento per l'operazione eseguita
     * @param string $mex
     * @return string
     */
    public function operationDone(string $mex): string
    {

        $mex = Filters::out($mex);

        switch ( $mex ) {
            case 'UpOk':
                $text = 'Abilità aumentata con successo.';
                break;
            case 'downOk':
                $text = 'Abilità diminuita correttamente.';
                break;
            case 'ExpKo':
                $text = 'Non hai abbastanza esperienza per l\'acquisto.';
                break;
            case 'ReqKo':
                $text = 'Non hai i requisiti necessari per questa abilita.';
                break;
            case 'PermKo':
                $text = 'Non hai i permessi per effettuare questa operazione.';
                break;
            default:
                $text = 'Errore sconosciuto, contattare lo staff.';
                break;
        }

        return $text;
    }

}
