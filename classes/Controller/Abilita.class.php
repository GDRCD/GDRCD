<?php

/**
 * @class Abilita
 * @note Classe per la gestione centralizzata delle abilita
 * @required PHP 7.1+
 */
class Abilita extends BaseClass
{

    private
        $abi_public,
        $abi_level_cap,
        $default_px_lvl,
        $abi_requirement,
        $requisito_abi,
        $requisito_stat,
        $abi_extra;

    public function __construct()
    {
        parent::__construct();

        # Le abilita sono pubbliche?
        $this->abi_public = Functions::get_constant('ABI_PUBLIC');

        # Livello massimo abilita
        $this->abi_level_cap = Functions::get_constant('ABI_LEVEL_CAP');

        # Default moltiplicatore livello, se costo non specificato in abi_extra
        $this->default_px_lvl = Functions::get_constant('DEFAULT_PX_PER_LVL');

        # I requisiti abilita' sono attivi?
        $this->abi_requirement = Functions::get_constant('ABI_REQUIREMENT');

        # Requisito di tipo abilita
        $this->requisito_abi = Functions::get_constant('REQUISITO_ABI');

        # Requisito di tipo statistica
        $this->requisito_stat = Functions::get_constant('REQUISITO_STAT');

        # I dati abilita' extra sono attivi?
        $this->abi_extra = Functions::get_constant('ABI_EXTRA');
    }

    /*** TABLE HELPER */

    /**
     * @fn getAbilita
     * @note Ottiene i dati di un'abilita'
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAbilita(int $id, string $val = '*'){
        return DB::query("SELECT {$val} FROM abilita WHERE id_abilita = '{$id}' LIMIT 1");
    }

    /**** CONTROLS ****/

    /**
     * @fn AbiVisibility
     * @note Controlla se la pagina abilita' e' publica o se si hanno i permessi per guardarla
     * @param string $pg
     * @return bool
     */
    public function AbiVisibility(string $pg): bool
    {
        # Se la costante e' true OR se non e' true: se sono il proprietario del pg OR sono moderatore+
        return ($this->abi_public || (Filters::out($pg) == $this->me) || ($this->permission >= MODERATOR));
    }

    /**
     * @fn extraActive
     * @note Controlla se la tabella `abilita_extra` e' attiva
     * @return bool
     */
    public function extraActive(): bool
    {
        return $this->abi_extra;
    }

    /**
     * @fn requirementActive
     * @note Controlla se la tabella `abilita_requisiti` e' attiva
     * @return bool
     */
    public function requirementActive(): bool
    {
        return $this->abi_requirement;
    }

    /**
     * @fn requirementControl
     * @param string $pg
     * @param int $abi
     * @param int $grado
     * @return bool
     */
    public function requirementControl(string $pg, int $abi, int $grado): bool
    {

        if ($this->requirementActive()) {

            $pg = Filters::int($pg);
            $abi = Filters::int($abi);
            $grado = Filters::int($grado);

            $requisiti = DB::query("SELECT abilita_requisiti.* FROM abilita_requisiti WHERE abilita='{$abi}' AND grado='{$grado}'", 'result');
            $esito = true;

            foreach ($requisiti as $requisito) {
                $tipo = Filters::int($requisito['tipo']);
                $rif = Filters::int($requisito['id_riferimento']);
                $rif_lvl = Filters::int($requisito['liv_riferimento']);

                switch ($tipo) {
                    case $this->requisito_abi:
                        $contr = DB::query("SELECT COUNT(id) as TOT 
                                        FROM personaggio_abilita 
                                        WHERE 
                                              personaggio_abilita.abilita = '{$rif}' 
                                          AND personaggio_abilita.personaggio='{$pg}' 
                                          AND personaggio_abilita.grado >= '{$rif_lvl}' LIMIT 1");

                        if ($contr['TOT'] == 0) {
                            $esito = false;
                        }

                        break;
                    case $this->requisito_stat:
                        $stat_pg = PersonaggioStats::getPgStat($rif,$pg,'valore');
                        $stat = Filters::int($stat_pg['valore']);

                        if ($stat < $rif_lvl) {
                            $esito = false;
                        }

                        break;
                }


            }

            return $esito;
        } else {
            return true;
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

        switch ($mex) {
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

    /***** DATI ABILITA *****/

    /**
     * @fn ListaAbilita
     * @note Ritorna una serie di option per una select contenente la lista abilita'
     * @return string
     */
    public function ListaAbilita(): string
    {
        $html = '';
        $news = DB::query("SELECT * FROM abilita WHERE 1 ORDER BY nome", 'result');

        foreach ($news as $new) {
            $nome = Filters::out($new['nome']);
            $id = Filters::int($new['id_abilita']);
            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        return $html;
    }

    /**
     * @fn AbilityList
     * @note Estrae la lista abilita, se specificato un pg ci associa anche il grado
     * @param string $pg
     * @return bool|int
     */
    public function AbilityList(string $pg = '')
    {

        $pg = Filters::out($pg);

        $left = !empty($pg) ?
            "LEFT JOIN personaggio_abilita ON (abilita.id_abilita = personaggio_abilita.abilita AND personaggio_abilita.personaggio='{$pg}')
               LEFT JOIN personaggio ON (personaggio.id='{$pg}')
            " :
            '';

        $extraVal = !empty($pg) ?
            ',personaggio_abilita.grado ' :
            '';

        $where = !empty($pg) ?
            '(abilita.id_razza = -1 OR abilita.id_razza = personaggio.id_razza)' :
            '1';

        return DB::query("SELECT abilita.* {$extraVal} FROM abilita {$left} WHERE {$where} ORDER BY abilita.nome", 'result');
    }

    /**
     * @fn abiExtra
     * @note Estrae i dati di un livello di abilita dalla tabella `abilita_extra`
     * @param int $abi
     * @param int $grado
     * @return mixed
     */
    public function abiExtra(int $abi, int $grado)
    {
        $abi = Filters::int($abi);
        $grado = Filters::int($grado);
        return DB::query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");
    }

    /**
     * @fn defaultCalcUp
     * @note Formula di default in caso di valore assente
     * @param int $grado
     * @return int
     */
    public function defaultCalcUp(int $grado): int
    {
        return round(Filters::int($grado) * $this->default_px_lvl);
    }

    /***** DATI ABILITA->PG ****/

    /**
     * @fn AbilitaPg
     * @note Estrae lista abilita pg
     * @param string $pg
     * @param int $abi
     * @return array|bool|int
     */
    public function AbilitaPg(string $pg, int $abi = 0)
    {
        $pg = Filters::int($pg);
        $id = Filters::int($abi);

        $extra = ($id > 0) ? " AND abilita='{$id}' LIMIT 1" : '';

        $data = DB::query("SELECT abilita, grado FROM personaggio_abilita WHERE personaggio='{$pg}' {$extra}", 'result');

        return ($id > 0) ? DB::query($data, 'fetch') : $data;
    }

    /**
     * @fn ExpPG
     * @note Estrae esperienza personaggio
     * @param string $pg
     * @return int
     */
    public function ExpPG(int $pg): int
    {
        $pg = Filters::int($pg);
        $exp = DB::query("SELECT esperienza FROM personaggio WHERE id='{$pg}' LIMIT 1");

        var_dump($pg);

        return Filters::int($exp['esperienza']);
    }

    /**
     * @fn RemainedExp
     * @note Esperienza Rimasta al pg
     * @param string $pg
     * @return float
     */
    public function RemainedExp(int $pg): float
    {
        # Filtro dati passati
        $pg = Filters::int($pg);

        # Estraggo abilita' ed esperienza pg
        $abi_pg = $this->AbilitaPg($pg);
        $exp_pg = $this->ExpPg($pg);

        # Inizio le variabili necessarie
        $px_spesi = 0;
        $count = 1;

        # Se sono abilitate le tabelle extra per le abilita'
        if ($this->extraActive()) {

            # Per ogni abilita del pg
            foreach ($abi_pg as $row_pg) {

                # Estraggo i dati dell'abilita
                $grado = Filters::int($row_pg['grado']);
                $abi_id = Filters::int($row_pg['id_abilita']);

                # Per ogni livello dell'abilita
                while ($count <= $grado) {

                    # Estraggo il costo
                    $extra = $this->abiExtra($abi_id, $count);

                    # Se non c'e' un costo, calcolo quello di default per quel livello
                    if (!empty($extra['costo']) && ($extra['costo'] > 0)) {
                        $px_abi = Filters::int($extra['costo']);
                    } else {
                        $px_abi = $this->defaultCalcUp($count);
                    }

                    # Aggiungo il costo calcolato
                    $px_spesi += $px_abi;
                    $count++;
                }

                # Restarto il counter per la prossima abilita
                $count = 1;
            }
        } else {

            # Per ogni abilita del pg
            foreach ($abi_pg as $row_pg) {

                # Estraggo il grado
                $grado = Filters::int($row_pg['grado']);

                # Per ogni livello
                while ($count <= $grado) {

                    # Calcolo il costo del livello
                    $px_abi = $this->defaultCalcUp($count);

                    # Lo aggiungo ai costi
                    $px_spesi += $px_abi;
                    $count++;
                }

                # Restarto il counter per la prossima abilita'
                $count = 1;
            }
        }

        # Ritorno la differenza tra l'esperienza del pg e quella spesa
        return round($exp_pg - $px_spesi);
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
        # Filter passed data
        $abi = Filters::int($abi);
        $grado = Filters::int($grado);

        # Controllo se non ho il livello massimo consentito
        $new_grado = ($grado < $this->abi_level_cap) ? ($grado + 1) : 0;

        # Create return array
        $default_descr = DB::query("SELECT descrizione FROM abilita WHERE id_abilita='{$abi}' LIMIT 1");
        $data = [
            'text' => Filters::html($default_descr['descrizione']),
            'requirement' => '',
            'lvl_extra_text' => '',
            'next_lvl_extra_text' => '',
            'next_price' => ''
        ];

        # Se i dati extra sono attivi
        if ($this->extraActive()) {

            # Estraggo la descrizione extra attuale
            $actual_descr = DB::query("SELECT descrizione FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            # Se esiste la descrizione extra, la aggiungo
            if (!empty($actual_descr['descrizione'])) {
                $data['lvl_extra_text'] = Filters::html($actual_descr['descrizione']);
            }

            # Se non ho il livello massimo
            if ($new_grado > 0) {

                # Estraggo la descrizione del livello successivo
                $next_descr = DB::query("SELECT descrizione,costo FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$new_grado}' LIMIT 1");
                $costo = Filters::int($next_descr['costo']);

                # Se esiste la descrizione extra del successivo, la aggiungo
                if (!empty($next_descr['descrizione'])) {
                    $data['next_lvl_extra_text'] = Filters::html($next_descr['descrizione']);
                }

                # Se il costo esiste ed e' maggiore di 0, lo setti, altrimenti usi il calcolo di default
                if (!empty($costo) && ($costo > 0)) {
                    $data['price'] = $costo;
                } else {
                    $data['price'] = $this->defaultCalcUp($new_grado);
                }
            }
        }

        # Se i requisiti sono attivi
        if ($this->requirementActive()) {

            # Li estraggo
            $requisiti = DB::query("SELECT abilita_requisiti.* FROM abilita_requisiti WHERE abilita='{$abi}' AND grado='{$new_grado}' ORDER BY tipo DESC", 'result');

            # Per ogni requisito
            foreach ($requisiti as $requisito) {

                # Estraggo i suoi dati
                $tipo = Filters::int($requisito['tipo']);
                $rif = Filters::int($requisito['id_riferimento']);
                $rif_lvl = Filters::int($requisito['liv_riferimento']);

                # Compongo l'html in base al tipo
                switch ($tipo) {
                    case $this->requisito_abi:
                        $req_data = DB::query("SELECT nome FROM abilita WHERE id_abilita='{$rif}' LIMIT 1");
                        $nome = Filters::out($req_data['nome']);

                        $data['requirement'] .= " {$nome} {$rif_lvl}, ";

                        break;
                    case $this->requisito_stat:
                        if(Statistiche::existStat($rif)) {
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


    /*** FUNCTIONS ***/

    /**
     * @fn upgradeSkillPermission
     * @note Controlla i permessi per l'upgrade delle skill
     * @param string $pg
     * @param int $grado
     * @return bool
     */
    public function upgradeSkillPermission(string $pg, int $grado): bool
    {
        $pg = Filters::int($pg);
        $grado = Filters::int($grado);
        $new_grado = Filters::int(($grado + 1));

        return (((Personaggio::isMyPg($pg)) || ($this->permission >= MODERATOR)) && ($new_grado <= $this->abi_level_cap));
    }

    /**
     * @fn upgradeskill
     * @note Aumenta una skill del pg
     * @param int $abi
     * @param string $pg
     * @return array
     */
    public function upgradeskill(int $abi, string $pg): array
    {
        $abi = Filters::int($abi);
        $pg = Filters::int($pg);
        $abi_pg = $this->AbilitaPg($pg, $abi);
        $grado = Filters::int($abi_pg['grado']);


        var_dump($pg);
        if ($this->upgradeSkillPermission($pg, $grado)) {

            $new_grado = Filters::int(($grado + 1));

            if ($this->requirementControl($pg, $abi, $new_grado)) {
                $exp_remained = $this->RemainedExp($pg);

                var_dump($exp_remained);

                if ($this->extraActive()) {

                    $extra = $this->abiExtra($abi, $new_grado);

                    if (!empty($extra['costo']) && ($extra['costo'] > 0)) {
                        $costo = Filters::int($extra['costo']);
                    } else {
                        $costo = $this->defaultCalcUp($new_grado);
                    }

                } else {
                    $costo = $this->defaultCalcUp($new_grado);
                }

                if ($exp_remained >= $costo) {

                    if ($grado == 0) {
                        DB::query("INSERT INTO personaggio_abilita(personaggio,abilita,grado) VALUES('{$pg}','{$abi}','{$new_grado}')");
                    } else {
                        DB::query("UPDATE personaggio_abilita SET grado='{$new_grado}' WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
                    }

                    return ['response' => true, 'mex' => 'UpOk'];
                } else {
                    return ['response' => false, 'mex' => 'ExpKo'];
                }
            } else {
                return ['response' => false, 'mex' => 'ReqKo'];
            }
        } else {
            return ['response' => false, 'mex' => 'PermKo'];
        }

    }

    /**
     * @fn downgradeSkillPermission
     * @note Controlla se si hanno i permessi per diminuire una skill
     * @param int $grado
     * @return bool
     */
    public function downgradeSkillPermission(int $grado): bool
    {
        $grado = Filters::int($grado);
        return (($this->permission >= MODERATOR) && ($grado > 0));
    }

    /**
     * @fn downgradeSkill
     * @note Diminuisce un'abilita' di un livello
     * @param int $abi
     * @param string $pg
     */
    public function downgradeSkill(int $abi, string $pg): array
    {
        $abi = Filters::int($abi);
        $pg = Filters::int($pg);

        $abi_data = $this->AbilitaPg($pg, $abi);
        $grado = Filters::int($abi_data['grado']);

        if ($this->downgradeSkillPermission($grado)) {

            $new_grado = ($grado - 1);

            if ($new_grado == 0) {
                DB::query("DELETE FROM personaggio_abilita WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
            } else {
                DB::query("UPDATE personaggio_abilita SET grado='{$new_grado}' WHERE personaggio='{$pg}' AND abilita='{$abi}' LIMIT 1");
            }

            return ['response' => true, 'mex' => 'downOk'];
        } else {
            return ['response' => false, 'mex' => 'PermKo'];
        }

    }

    /***** GESTIONE ABILITA EXTRA *****/

    /**
     * @fn AbiExtraManagePermission
     * @note Permessi per la gestione della tabella abilita_extra
     * @return bool
     */
    function AbiExtraManagePermission(): bool
    {
        return ($_SESSION['permessi'] >= SUPERUSER);
    }

    /**
     * @fn DatiAbiExtra
     * @note Estrazione dinamica dati di una riga nella tabella abilita_extra
     * @param array $post
     * @return array
     */
    public function DatiAbiExtra(array $post): array
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);

            $data = DB::query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if (!empty($data['abilita'])) {
                $descr = Filters::in($data['descrizione']);
                $costo = Filters::int($data['costo']);

                return ['response' => true, 'Descr' => $descr, 'Costo' => $costo];
            } else {
                return ['response' => false];
            }
        } else {
            return ['response' => false];
        }

    }

    /**
     * @fn NewAbiExtra
     * @note Aggiunta di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function NewAbiExtra(array $post): bool
    {

        if ($this->AbiExtraManagePermission()) {
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);

            $contr = DB::query("SELECT count(id) as TOT FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if ($contr['TOT'] == 0) {
                DB::query("INSERT INTO abilita_extra(abilita,grado,descrizione,costo) VALUES('{$abi}','{$grado}','{$descr}','{$costo}')");
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn ModAbiExtra
     * @note Modifica di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function ModAbiExtra(array $post): bool
    {

        if ($this->AbiExtraManagePermission()) {
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);

            DB::query("UPDATE abilita_extra SET abilita='{$abi}',grado='{$grado}',descrizione='{$descr}',costo='{$costo}' WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn DelAbiExtra
     * @note Eliminazione di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function DelAbiExtra(array $post): bool
    {

        if ($this->AbiExtraManagePermission()) {
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);

            DB::query("DELETE FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

    /***** GESTIONE REQUISITI ABILITA ****/

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @return string
     * @example return : <option value='id'>nome</option>
     */
    public function ListaRequisiti(): string
    {
        global $PARAMETERS;
        $html = '';
        $abi_req_abi = DB::query("
            SELECT abilita_requisiti.*,abilita.nome FROM abilita_requisiti 
                LEFT JOIN abilita ON (abilita.id_abilita = abilita_requisiti.abilita) 
            WHERE tipo=1 ORDER BY abilita.nome", 'result');


        $html .= '<optgroup label="Abilita">';
        foreach ($abi_req_abi as $new) {
            $id = Filters::int($new['id']);
            $id_riferimento = Filters::int($new['id_riferimento']);
            $lvl_rif = Filters::int($new['liv_riferimento']);
            $grado = Filters::int($new['grado']);
            $nome_abi = Filters::out($new['nome']);

            $dati_rif = DB::query("SELECT nome FROM abilita WHERE id_abilita='{$id_riferimento}' LIMIT 1");

            $nome_riferimento = Filters::out($dati_rif['nome']);

            $html .= "<option value='{$id}'>{$nome_abi} {$grado} - {$nome_riferimento} {$lvl_rif}</option>";
        }
        $html .= "</optgroup>";

        $abi_req_stat = DB::query("
            SELECT abilita_requisiti.*,abilita.nome FROM abilita_requisiti 
            LEFT JOIN abilita ON (abilita.id_abilita = abilita_requisiti.abilita) 
            WHERE tipo=2 ORDER BY abilita.nome", 'result');

        $html .= '<optgroup label="Caratteristiche">';

        foreach ($abi_req_stat as $new) {
            $id = Filters::int($new['id']);

            $id_riferimento = Filters::int($new['id_riferimento']);
            $lvl_rif = Filters::int($new['liv_riferimento']);
            $grado = Filters::int($new['grado']);
            $nome_abi = Filters::out($new['nome']);

            $nome_riferimento = $PARAMETERS['names']['stats']['car' . $id_riferimento];

            $html .= "<option value='{$id}'>{$nome_abi} {$grado} - {$nome_riferimento} {$lvl_rif}</option>";
        }
        $html .= "</optgroup>";

        return $html;

    }

    /**
     * @fn DatiAbiRequisito
     * @note Estrae i dati di un requisito abilita
     * @param array $post
     * @return array|false[]|void
     */
    public function DatiAbiRequisito(array $post)
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $id = Filters::int($post['id']);

            $data = DB::query("SELECT * FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");

            if (!empty($data['abilita'])) {
                $abi = Filters::int($data['abilita']);
                $grado = Filters::int($data['grado']);
                $tipo = Filters::int($data['tipo']);
                $id_rif = Filters::int($data['id_riferimento']);
                $lvl_rif = Filters::int($data['liv_riferimento']);

                return ['response' => true, 'Abi' => $abi, 'Grado' => $grado, 'Tipo' => $tipo, 'IdRif' => $id_rif, 'LivRif' => $lvl_rif];
            } else {
                return ['response' => false];
            }
        }

    }

    /**
     * @fn NewAbiRequisito
     * @note Crea un requisito per abilita'
     * @param array $post
     * @return bool
     */
    public function NewAbiRequisito(array $post): bool
    {
        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);
            $tipo = Filters::int($post['tipo']);
            $id_rif = Filters::int($post['id_req']);
            $lvl_rif = Filters::int($post['liv_req']);

            DB::query("INSERT INTO abilita_requisiti(abilita,grado,tipo,id_riferimento,liv_riferimento) VALUES('{$abi}','{$grado}','{$tipo}','{$id_rif}','{$lvl_rif}')");

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn ModAbiRequisito
     * @note Modifica un requisito per abilita'
     * @param array $post
     * @return bool
     */
    public function ModAbiRequisito(array $post): bool
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $id = Filters::int($post['req_id']);
            $abi = Filters::int($post['abi']);
            $grado = Filters::int($post['grado']);
            $tipo = Filters::int($post['tipo']);
            $id_rif = Filters::int($post['id_req']);
            $lvl_rif = Filters::int($post['liv_req']);

            DB::query("UPDATE abilita_requisiti SET abilita='{$abi}',grado='{$grado}',tipo='{$tipo}',id_riferimento='{$id_rif}',liv_riferimento='{$lvl_rif}' WHERE id='{$id}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn DelAbiRequisito
     * @note Elimina un requisito per abilita'
     * @param array $post
     * @return bool
     */
    public function DelAbiRequisito(array $post): bool
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {

            $id = Filters::int($post['req_id']);
            DB::query("DELETE FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

}