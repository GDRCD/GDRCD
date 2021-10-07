<?php

/**
 * @class Abilita
 * @note Classe per la gestione centralizzata delle abilita
 * @required PHP 7.1+
 */
class Abilita
{

    private $me,
        $permessi;

    public function __construct()
    {
        $this->me = gdrcd_filter('in', $_SESSION['login']);
        $this->permessi = gdrcd_filter('num', $_SESSION['permessi']);
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

        $pg = gdrcd_filter('out', $pg);

        # Se non esiste la costante OR se e' true OR se non e' true: se sono il proprietario del pg OR sono moderatore
        return (!defined('ABI_PUBLIC') || (ABI_PUBLIC) || ($pg == $this->me) || ($this->permessi >= MODERATOR));
    }

    /**
     * @fn extreActive
     * @note Controlla se la tabella `abilita_extra` e' attiva
     * @return bool
     */
    public function extraActive(): bool
    {
        return (defined('ABI_EXTRA') && ABI_EXTRA);
    }

    /**
     * @fn requirementActive
     * @note Controlla se la tabella `abilita_requisiti` e' attiva
     * @return bool
     */
    public function requirementActive(): bool
    {
        return (defined('ABI_REQUIREMENT') && ABI_REQUIREMENT);
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

            $pg = gdrcd_filter('out', $pg);
            $abi = gdrcd_filter('num', $abi);
            $grado = gdrcd_filter('num', $grado);

            $requisiti = gdrcd_query("SELECT abilita_requisiti.* FROM abilita_requisiti WHERE abilita='{$abi}' AND grado='{$grado}'", 'result');
            $esito = true;

            foreach ($requisiti as $requisito) {
                $tipo = gdrcd_filter('num', $requisito['tipo']);
                $rif = gdrcd_filter('num', $requisito['id_riferimento']);
                $rif_lvl = gdrcd_filter('num', $requisito['liv_riferimento']);

                switch ($tipo) {
                    case REQUISITO_ABI:
                        $contr = gdrcd_query("SELECT COUNT(id_abilita) as TOT 
                                        FROM clgpersonaggioabilita 
                                        WHERE 
                                              clgpersonaggioabilita.id_abilita = '{$rif}' 
                                          AND clgpersonaggioabilita.nome='{$pg}' 
                                          AND clgpersonaggioabilita.grado >= '{$rif_lvl}' LIMIT 1");

                        if ($contr['TOT'] == 0) {
                            $esito = false;
                        }

                        break;
                    case REQUISITO_STAT:

                        $contr = gdrcd_query("SELECT car{$rif} FROM personaggio WHERE nome='{$pg}' LIMIT 1");
                        $stat = gdrcd_filter('num', $contr['car' . $rif]);

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

    /***** DATI ABILITA *****/

    /**
     * @fn ListaAbilita
     * @note Ritorna una serie di option per una select contenente la lista abilita'
     * @return string
     */
    public function ListaAbilita(): string
    {
        $html = '';
        $news = gdrcd_query("SELECT * FROM abilita WHERE 1 ORDER BY nome", 'result');

        foreach ($news as $new) {
            $nome = gdrcd_filter('out', $new['nome']);
            $id = gdrcd_filter('num', $new['id_abilita']);
            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        return $html;
    }

    /**
     * @fn AbilityList
     * @note Estrae la lista abilita, se specificato un pg ci associa anche il grado
     * @param string $pg
     * @return bool|int|un
     */
    public function AbilityList(string $pg = '')
    {

        $pg = gdrcd_filter('in', $pg);

        $left = !empty($pg) ?
            "LEFT JOIN clgpersonaggioabilita ON (abilita.id_abilita = clgpersonaggioabilita.id_abilita AND clgpersonaggioabilita.nome='{$pg}')
               LEFT JOIN personaggio ON (personaggio.nome='{$pg}')
            " :
            '';

        $extraVal = !empty($pg) ?
            ',clgpersonaggioabilita.grado ' :
            '';

        $where = !empty($pg) ?
            '(abilita.id_razza = -1 OR abilita.id_razza = personaggio.id_razza)' :
            '1';

        return gdrcd_query("SELECT abilita.* {$extraVal} FROM abilita {$left} WHERE {$where} ORDER BY abilita.nome", 'result');
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
        $abi = gdrcd_filter('num', $abi);
        $grado = gdrcd_filter('num', $grado);
        return gdrcd_query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");
    }

    /***** DATI ABILITA->PG ****/

    /**
     * @fn AbilitaPg
     * @note Estrae lista abilita pg
     * @param string $pg
     * @param int $abi
     * @return array|bool|int|un
     */
    public function AbilitaPg(string $pg, int $abi = 0)
    {
        $pg = gdrcd_filter('in', $pg);

        $id = gdrcd_filter('num', $abi);

        $extra = ($id > 0) ? " AND id_abilita='{$id}' LIMIT 1" : '';

        $data = gdrcd_query("SELECT id_abilita, grado FROM clgpersonaggioabilita WHERE nome='{$pg}' {$extra}", 'result');

        return ($id > 0) ? gdrcd_query($data, 'fetch') : $data;
    }

    /**
     * @fn ExpPG
     * @note Estrae esperienza personaggio
     * @param string $pg
     * @return int
     */
    public function ExpPG(string $pg): int
    {
        $pg = gdrcd_filter('in', $pg);
        $exp = gdrcd_query("SELECT esperienza FROM personaggio WHERE nome='{$pg}' LIMIT 1");

        return gdrcd_filter('num', $exp['esperienza']);
    }

    /**
     * @fn RemainedExp
     * @note Esperienza Rimasta al pg
     * @param string $pg
     * @return float
     */
    public function RemainedExp(string $pg): float
    {
        # Filtro dati passati
        $pg = gdrcd_filter('in', $pg);

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
                $grado = gdrcd_filter('num', $row_pg['grado']);
                $abi_id = gdrcd_filter('num', $row_pg['id_abilita']);

                # Per ogni livello dell'abilita
                while ($count <= $grado) {

                    # Estraggo il costo
                    $extra = $this->abiExtra($abi_id, $count);

                    # Se non c'e' un costo, calcolo quello di default per quel livello
                    if (!empty($extra['costo']) && ($extra['costo'] > 0)) {
                        $px_abi = gdrcd_filter('num', $extra['costo']);
                    } else {
                        $px_abi = (DEFAULT_PX_PER_LVL * $count);
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
                $grado = gdrcd_filter('num', $row_pg['grado']);

                # Per ogni livello
                while ($count <= $grado) {

                    # Calcolo il costo del livello
                    $px_abi = (DEFAULT_PX_PER_LVL * $count);

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
        $pg = gdrcd_filter('in', $pg);
        $grado = gdrcd_filter('num', $grado);
        $new_grado = gdrcd_filter('num', ($grado + 1));

        return ((($this->me == $pg) || ($this->permessi >= MODERATOR)) && ($new_grado <= ABI_LEVEL_CAP));
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

        $abi = gdrcd_filter('num', $abi);
        $pg = gdrcd_filter('in', $pg);
        $abi_pg = $this->AbilitaPg($pg, $abi);
        $grado = gdrcd_filter('num', $abi_pg['grado']);

        if ($this->upgradeSkillPermission($pg, $grado)) {

            $new_grado = gdrcd_filter('num', ($grado + 1));

            if ($this->requirementControl($pg, $abi, $new_grado)) {
                $exp_remained = $this->RemainedExp($pg);

                if ($this->extraActive()) {

                    $extra = $this->abiExtra($abi, $new_grado);

                    if (!empty($extra['costo']) && ($extra['costo'] > 0)) {
                        $costo = gdrcd_filter('num', $extra['costo']);
                    } else {
                        $costo = (DEFAULT_PX_PER_LVL * $new_grado);
                    }

                } else {
                    $costo = (DEFAULT_PX_PER_LVL * $new_grado);
                }

                if ($exp_remained >= $costo) {

                    if ($grado == 0) {
                        gdrcd_query("INSERT INTO clgpersonaggioabilita(nome,id_abilita,grado) VALUES('{$pg}','{$abi}','{$new_grado}')");
                    } else {
                        gdrcd_query("UPDATE clgpersonaggioabilita SET grado='{$new_grado}' WHERE nome='{$pg}' AND id_abilita='{$abi}' LIMIT 1");
                    }

                    return ['response' => true, 'mex' => 'Abilità aumentata con successo.'];
                } else {
                    return ['response' => false, 'mex' => 'Non hai abbastanza esperienza per l\'acquisto.'];
                }
            } else {
                return ['response' => false, 'mex' => 'Non hai i requisiti necessari per questa abilita.'];
            }
        } else {
            return ['response' => false, 'mex' => 'Non hai i permessi per gestire questo personaggio o hai raggiunto il livello massimo.'];
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
        $grado = gdrcd_filter('num', $grado);
        return (($this->permessi >= MODERATOR) && ($grado > 0));
    }

    /**
     * @fn downgradeSkill
     * @note Diminuisce un'abilita' di un livello
     * @param int $abi
     * @param string $pg
     */
    public function downgradeSkill(int $abi, string $pg): array
    {

        $abi = gdrcd_filter('num', $abi);
        $pg = gdrcd_filter('in', $pg);

        $abi_data = $this->AbilitaPg($pg, $abi);
        $grado = gdrcd_filter('num', $abi_data['grado']);

        if ($this->downgradeSkillPermission($grado)) {

            $new_grado = ($grado - 1);

            if ($new_grado == 0) {
                gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome='{$pg}' AND id_abilita='{$abi}' LIMIT 1");
            } else {
                gdrcd_query("UPDATE clgpersonaggioabilita SET grado='{$new_grado}' WHERE nome='{$pg}' AND id_abilita='{$abi}' LIMIT 1");
            }

            return ['response' => true, 'mex' => 'Abilità diminuita correttamente.'];
        } else {
            return ['response' => false, 'mex' => 'Non puoi diminuire quest\'abilità.'];
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
            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);

            $data = gdrcd_query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if (!empty($data['abilita'])) {
                $descr = gdrcd_filter('in', $data['descrizione']);
                $costo = gdrcd_filter('num', $data['costo']);

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

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $descr = gdrcd_filter('in', $post['descr']);
            $costo = gdrcd_filter('num', $post['costo']);

            $contr = gdrcd_query("SELECT count(id) as TOT FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if ($contr['TOT'] == 0) {
                gdrcd_query("INSERT INTO abilita_extra(abilita,grado,descrizione,costo) VALUES('{$abi}','{$grado}','{$descr}','{$costo}')");
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

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $descr = gdrcd_filter('in', $post['descr']);
            $costo = gdrcd_filter('num', $post['costo']);

            gdrcd_query("UPDATE abilita_extra SET abilita='{$abi}',grado='{$grado}',descrizione='{$descr}',costo='{$costo}' WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

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

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);

            gdrcd_query("DELETE FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

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
        $abi_req_abi = gdrcd_query("
            SELECT abilita_requisiti.*,abilita.nome FROM abilita_requisiti 
                LEFT JOIN abilita ON (abilita.id_abilita = abilita_requisiti.abilita) 
            WHERE tipo=1 ORDER BY abilita.nome", 'result');


        $html .= '<optgroup label="Abilita">';
        foreach ($abi_req_abi as $new) {

            $id = gdrcd_filter('num', $new['id']);

            $id_riferimento = gdrcd_filter('num', $new['id_riferimento']);
            $lvl_rif = gdrcd_filter('num', $new['liv_riferimento']);
            $grado = gdrcd_filter('num', $new['grado']);
            $nome_abi = gdrcd_filter('out', $new['nome']);

            $dati_rif = gdrcd_query("SELECT nome FROM abilita WHERE id_abilita='{$id_riferimento}' LIMIT 1");

            $nome_riferimento = gdrcd_filter('out', $dati_rif['nome']);

            $html .= "<option value='{$id}'>{$nome_abi} {$grado} - {$nome_riferimento} {$lvl_rif}</option>";
        }
        $html .= "</optgroup>";

        $abi_req_stat = gdrcd_query("
            SELECT abilita_requisiti.*,abilita.nome FROM abilita_requisiti 
            LEFT JOIN abilita ON (abilita.id_abilita = abilita_requisiti.abilita) 
            WHERE tipo=2 ORDER BY abilita.nome", 'result');

        $html .= '<optgroup label="Caratteristiche">';
        foreach ($abi_req_stat as $new) {

            $id = gdrcd_filter('num', $new['id']);

            $id_riferimento = gdrcd_filter('num', $new['id_riferimento']);
            $lvl_rif = gdrcd_filter('num', $new['liv_riferimento']);
            $grado = gdrcd_filter('num', $new['grado']);
            $nome_abi = gdrcd_filter('out', $new['nome']);

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
            $id = gdrcd_filter('num', $post['id']);

            $data = gdrcd_query("SELECT * FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");

            if (!empty($data['abilita'])) {
                $abi = gdrcd_filter('num', $data['abilita']);
                $grado = gdrcd_filter('num', $data['grado']);
                $tipo = gdrcd_filter('num', $data['tipo']);
                $id_rif = gdrcd_filter('num', $data['id_riferimento']);
                $lvl_rif = gdrcd_filter('num', $data['liv_riferimento']);

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

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $tipo = gdrcd_filter('num', $post['tipo']);
            $id_rif = gdrcd_filter('num', $post['id_req']);
            $lvl_rif = gdrcd_filter('num', $post['liv_req']);

            gdrcd_query("INSERT INTO abilita_requisiti(abilita,grado,tipo,id_riferimento,liv_riferimento) VALUES('{$abi}','{$grado}','{$tipo}','{$id_rif}','{$lvl_rif}')");

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

            $id = gdrcd_filter('num', $post['req_id']);
            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $tipo = gdrcd_filter('num', $post['tipo']);
            $id_rif = gdrcd_filter('num', $post['id_req']);
            $lvl_rif = gdrcd_filter('num', $post['liv_req']);

            gdrcd_query("UPDATE abilita_requisiti SET abilita='{$abi}',grado='{$grado}',tipo='{$tipo}',id_riferimento='{$id_rif}',liv_riferimento='{$lvl_rif}' WHERE id='{$id}' LIMIT 1");

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
            $id = gdrcd_filter('num', $post['req_id']);

            gdrcd_query("DELETE FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

}