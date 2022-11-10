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
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getRequisitiByRow(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM abilita_requisiti WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAbilitaRequisiti
     * @note Ottiene i requisiti di un'abilita'
     * @param int $id
     * @param int $grado
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getRequisitiByGrado(int $id, int $grado, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM abilita_requisiti WHERE abilita = :id AND grado = :grado ",
            ['id' => $id, 'grado' => $grado]
        );
    }

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @param int $type
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getRequisitiByType(int $type, string $val = 'abilita_requisiti.*,abilita.nome'): DBQueryInterface
    {
        return DB::queryStmt("
            SELECT {$val} FROM abilita_requisiti 
                LEFT JOIN abilita ON (abilita.id = abilita_requisiti.abilita) 
            WHERE tipo=  :tipo ORDER BY abilita.nome",
            ['tipo' => $type]
        );
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
     * @note Controlla se il tipo di requisito è un abilita'
     * @param string $type
     * @return bool
     */
    public function isTypeAbilita(string $type): bool
    {
        return ($type == $this->requisito_abi);
    }

    /**
     * @fn isTypeStat
     * @note Controlla se il tipo di requisito è una statistica
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
     * @throws Throwable
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
     * @throws Throwable
     */
    public function pgRequisitoAbilitaControl(int $abi, int $pg, int $grado): bool
    {
        $count = DB::queryStmt(
            "SELECT COUNT(id) as TOT FROM personaggio_abilita 
                  WHERE personaggio_abilita.abilita = :abi AND personaggio_abilita.personaggio= :pg
                  AND personaggio_abilita.grado >= :grado LIMIT 1",
            ['abi' => $abi, 'pg' => $pg, 'grado' => $grado]
        );

        return ($count['TOT'] > 0);
    }

    /**
     * @fn pgRequisitoStatControl
     * @note Controlla se un pg super il requisito statistica
     * @param int $stat
     * @param int $pg
     * @param int $grado
     * @return bool
     * @throws Throwable
     */
    public function pgRequisitoStatControl(int $stat, int $pg, int $grado): bool
    {
        $stat_pg = PersonaggioStats::getPgStat($stat, $pg, 'valore');
        return (Filters::int($stat_pg['valore']) >= $grado);
    }

    /*** LIST ***/

    /**
     * @fn ListaRequisiti
     * @note Lista dei requisiti abilita', ritorna valori per una select
     * @return string
     * @throws Throwable
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

            $dati_rif = $this->getAbility($id_riferimento, 'nome');
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
        $options = [
            ["id" => $this->requisito_abi, "nome" => 'Abilita'],
            ["id" => $this->requisito_stat, "nome" => 'Statistica'],
        ];

        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $options);

    }

    /*** AJAX ***/

    /**
     * @fn DatiAbiRequisito
     * @note Estrae i dati di un requisito abilita
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxReqData(array $post): array
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

        return [
            "response" => false,
        ];
    }

    /**
     * @fn ajaxReqList
     * @note Estrae i dati di un requisito abilita
     * @return array
     * @throws Throwable
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
     * @throws Throwable
     */
    public function NewAbiRequisito(array $post): array
    {
        if ( $this->permissionManageAbiRequirement() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $tipo = Filters::int($post['tipo']);
            $id_rif = Filters::int($post['id_rif']);
            $lvl_rif = Filters::int($post['lvl_rif']);

            DB::queryStmt(
                "INSERT INTO abilita_requisiti(abilita,grado,tipo,id_riferimento,liv_riferimento) VALUES(:abi,:grado,:tipo,:rif,:lvl)",
                [
                    'abi' => $abi,
                    'grado' => $grado,
                    'tipo' => $tipo,
                    'rif' => $id_rif,
                    'lvl' => $lvl_rif,
                ]);

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
     * @throws Throwable
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

            DB::queryStmt(
                "UPDATE abilita_requisiti SET abilita=:abi,grado=:grado,tipo=:tipo,id_riferimento=:rif,liv_riferimento=:lvl WHERE id=:id",
                [
                    'id' => $id,
                    'abi' => $abi,
                    'grado' => $grado,
                    'tipo' => $tipo,
                    'rif' => $id_rif,
                    'lvl' => $lvl_rif,
                ]
            );

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
     * @throws Throwable
     */
    public function DelAbiRequisito(array $post): array
    {
        if ( $this->permissionManageAbiRequirement() ) {

            $id = Filters::int($post['requisito']);

            DB::queryStmt(
                "DELETE FROM abilita_requisiti WHERE id=:id",
                [
                    'id' => $id,
                ]
            );

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