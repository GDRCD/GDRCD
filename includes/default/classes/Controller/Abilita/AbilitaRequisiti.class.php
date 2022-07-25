<?php

/**
 * @class AbilitaRequisiti
 * @note Classe per la gestione centralizzata dei requisiti abilita
 * @required PHP 7.1+
 */
class AbilitaRequisiti extends Abilita
{

    /*** TABLE HELPER */

    /**
     * @fn getAbilitaRequisiti
     * @note Ottiene i requisiti di un'abilita'
     * @param int $id
     * @param int $grado
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getRequisitiByRow(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAbilitaRequisiti
     * @note Ottiene i requisiti di un'abilita'
     * @param int $id
     * @param int $grado
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getRequisitiByGrado(int $id, int $grado, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM abilita_requisiti WHERE abilita = '{$id}' AND grado ='{$grado}'", 'result');
    }

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @return bool|int|mixed|string
     */
    public function getRequisitiByType($type, $val = 'abilita_requisiti.*,abilita.nome')
    {
        return DB::query("
            SELECT {$val} FROM abilita_requisiti 
                LEFT JOIN abilita ON (abilita.id = abilita_requisiti.abilita) 
            WHERE tipo= '{$type}' ORDER BY abilita.nome", 'result');
    }

    /*** PERMISSIONS ***/

    /**
     * @fn permissionManageAbiRequirement
     * @note Controlla se si hanno i permessi per gestire i requisiti
     * @return bool
     */
    public function permissionManageAbiRequirement(): bool
    {
        return Permissions::permission('MANAGE_ABILITY_REQUIREMENT');
    }

    /**** CONTROLS ***/

    /**
     * @fn isTypeAbilita
     * @note Controlla se il tipo di requisito e' un abilita'
     * @param string $type
     * @return bool
     */
    public function isTypeAbilita(string $type): bool
    {
        return ($type == $this->requisito_abi);
    }

    /**
     * @fn isTypeStat
     * @note Controlla se il tipo di requisito e' una statistica
     * @param string $type
     * @return bool
     */
    public function isTypeStat(string $type): bool
    {
        return ($type == $this->requisito_stat);
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

        if ( $this->requirementActive() ) {

            $pg = Filters::int($pg);
            $abi = Filters::int($abi);
            $grado = Filters::int($grado);

            $requisiti = $this->getRequisitiByGrado($abi, $grado);
            $esito = true;

            foreach ( $requisiti as $requisito ) {
                $tipo = Filters::int($requisito['tipo']);
                $rif = Filters::int($requisito['id_riferimento']);
                $rif_lvl = Filters::int($requisito['liv_riferimento']);

                switch ( true ) {
                    case $this->isTypeAbilita($tipo):
                        if ( !$this->pgRequisitoAbilitaControl($rif, $pg, $rif_lvl) ) {
                            $esito = false;
                        }
                        break;
                    case $this->isTypeStat($tipo):
                        if ( !$this->pgRequisitoStatControl($rif, $pg, $rif_lvl) ) {
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

    /*** FUNCTIONS  */

    /**
     * @fn pgRequisitoAbilitaControl
     * @note Controlla se un pg super il requisito abilita
     * @param int $abi
     * @param int $pg
     * @param int $grado
     * @return bool
     */
    public function pgRequisitoAbilitaControl(int $abi, int $pg, int $grado): bool
    {
        $count = DB::query("SELECT COUNT(id) as TOT 
                                        FROM personaggio_abilita 
                                        WHERE 
                                              personaggio_abilita.abilita = '{$abi}' 
                                          AND personaggio_abilita.personaggio='{$pg}' 
                                          AND personaggio_abilita.grado >= '{$grado}' LIMIT 1");

        return ($count['TOT'] > 0);
    }

    /**
     * @fn pgRequisitoStatControl
     * @note Controlla se un pg super il requisito statistica
     * @param int $stat
     * @param int $pg
     * @param int $grado
     * @return bool
     */
    public function pgRequisitoStatControl(int $stat, int $pg, int $grado): bool
    {
        $stat_pg = PersonaggioStats::getPgStat($stat, $pg, 'valore');
        $stat = Filters::int($stat_pg['valore']);

        return ($stat >= $grado);
    }

    /*** LIST ***/

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @return string
     * @example return : <option value='id'>nome</option>
     */
    public function listRequisiti(): string
    {
        $html = '<option value=""></option>';
        $stat_class = Statistiche::getInstance();

        $abi_req_abi = $this->getRequisitiByType($this->requisito_abi);

        $html .= '<optgroup label="Abilita">';
        foreach ( $abi_req_abi as $new ) {
            $id = Filters::int($new['id']);
            $id_riferimento = Filters::int($new['id_riferimento']);
            $lvl_rif = Filters::int($new['liv_riferimento']);
            $grado = Filters::int($new['grado']);
            $nome_abi = Filters::out($new['nome']);

            $dati_rif = $this->getAbilita($id_riferimento, 'nome');
            $nome_riferimento = Filters::out($dati_rif['nome']);

            $html .= "<option value='{$id}'>{$nome_abi} {$grado} - {$nome_riferimento} {$lvl_rif}</option>";
        }
        $html .= "</optgroup>";

        $abi_req_stat = $this->getRequisitiByType($this->requisito_stat);

        $html .= '<optgroup label="Caratteristiche">';

        foreach ( $abi_req_stat as $new ) {
            $id = Filters::int($new['id']);

            $id_riferimento = Filters::int($new['id_riferimento']);
            $lvl_rif = Filters::int($new['liv_riferimento']);
            $grado = Filters::int($new['grado']);
            $nome_abi = Filters::out($new['nome']);
            $stat_data = $stat_class->getStat($id_riferimento, 'nome');

            $nome_riferimento = Filters::out($stat_data['nome']);

            $html .= "<option value='{$id}'>{$nome_abi} {$grado} - {$nome_riferimento} {$lvl_rif}</option>";
        }
        $html .= "</optgroup>";

        return $html;

    }

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @return string
     * @example return : <option value='id'>nome</option>
     */
    public function listRequisitiType(): string
    {
        $html = "<option value='{$this->requisito_abi}'>Abilita</option>";
        $html .= "<option value='{$this->requisito_stat}'>Statistica</option>";
        return $html;

    }

    /*** AJAX ***/

    /**
     * @fn DatiAbiRequisito
     * @note Estrae i dati di un requisito abilita
     * @param array $post
     * @return array|false[]|void
     */
    public function ajaxReqData(array $post)
    {

        if ( $this->permissionManageAbiRequirement() ) {
            $id = Filters::int($post['id']);

            $data = $this->getRequisitiByRow($id);

            $abi = Filters::int($data['abilita']);
            $grado = Filters::int($data['grado']);
            $tipo = Filters::int($data['tipo']);
            $id_rif = Filters::int($data['id_riferimento']);
            $lvl_rif = Filters::int($data['liv_riferimento']);

            return [
                'response' => true,
                'abilita' => $abi,
                'grado' => $grado,
                'tipo' => $tipo,
                'id_rif' => $id_rif,
                'lvl_rif' => $lvl_rif,
            ];

        }

    }

    /**
     * @fn ajaxReqList
     * @note Estrae i dati di un requisito abilita
     * @return array
     */
    public function ajaxReqList(): array
    {
        return ['List' => $this->listRequisiti()];
    }

    /***** GESTIONE  ****/

    /**
     * @fn NewAbiRequisito
     * @note Crea un requisito per abilita'
     * @param array $post
     * @return array
     */
    public function NewAbiRequisito(array $post): array
    {
        if ( $this->permissionManageAbiRequirement() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $tipo = Filters::int($post['tipo']);
            $id_rif = Filters::int($post['id_rif']);
            $lvl_rif = Filters::int($post['lvl_rif']);

            DB::query("INSERT INTO abilita_requisiti(abilita,grado,tipo,id_riferimento,liv_riferimento) VALUES('{$abi}','{$grado}','{$tipo}','{$id_rif}','{$lvl_rif}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nuovo requisito abilità creato.',
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
     * @fn ModAbiRequisito
     * @note Modifica un requisito per abilita'
     * @param array $post
     * @return array
     */
    public function ModAbiRequisito(array $post): array
    {

        if ( $this->permissionManageAbiRequirement() ) {
            $id = Filters::int($post['requisito']);
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $tipo = Filters::int($post['tipo']);
            $id_rif = Filters::int($post['id_rif']);
            $lvl_rif = Filters::int($post['lvl_rif']);

            DB::query("UPDATE abilita_requisiti SET abilita='{$abi}',grado='{$grado}',tipo='{$tipo}',id_riferimento='{$id_rif}',liv_riferimento='{$lvl_rif}' WHERE id='{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Requisito abilità modificato.',
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
     * @fn DelAbiRequisito
     * @note Elimina un requisito per abilita'
     * @param array $post
     * @return array
     */
    public function DelAbiRequisito(array $post): array
    {
        if ( $this->permissionManageAbiRequirement() ) {

            $id = Filters::int($post['requisito']);
            DB::query("DELETE FROM abilita_requisiti WHERE id='{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Requisito abilità eliminato.',
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
}