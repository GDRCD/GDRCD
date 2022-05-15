<?php
/**
 * @class GruppiTipi
 * @note Classe che gestisce i tipi di gruppo
 */
class GruppiTipi extends Gruppi
{

    protected function __construct()
    {
        parent::__construct();
    }

    public function permissionManageTypes(){
        return Permissions::permission('MANAGE_GROUPS');
    }

    public function getType(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_tipo WHERE id='{$id}' LIMIT 1");
    }

    public function getAllTypes(string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_tipo WHERE 1",'result');
    }

    /**
     * @fn listTypes
     * @note Genera gli option per i tipi di gruppo
     * @return string
     */
    public function listTypes(): string
    {
        $types = $this->getAllTypes();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $types);
    }


    public function ajaxTypeData(array $post):array{
        $id = Filters::int($post['id']);
        return $this->getType($id);
    }
    /** GESTIONE */

    /**
     * @fn NewType
     * @note Inserisce un tipo gruppo
     * @param array $post
     * @return array
     */
    public function NewType(array $post): array
    {
        if ($this->permissionManageTypes()) {

            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);


            DB::query("INSERT INTO gruppi_tipo (nome,descrizione )  
                        VALUES ('{$nome}','{$descr}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo creato.',
                'swal_type' => 'success',
                'types_list'=>$this->listTypes()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn ModRole
     * @note Aggiorna un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function ModType(array $post): array
    {
        if($this->permissionManageTypes()){
            $id=Filters::in( $post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);


            DB::query("UPDATE  gruppi_tipo 
                SET nome = '{$nome}', 
                    descrizione='{$descr}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo modificato.',
                'swal_type' => 'success',
                'types_list'=>$this->listTypes()
            ];
        } else{
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn DelRole
     * @note Cancella un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function DelType(array $post):array
    {
        if($this->permissionManageTypes()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi_tipo WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo eliminato.',
                'swal_type' => 'success',
                'types_list'=>$this->listTypes()
            ];
        } else{
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }
}