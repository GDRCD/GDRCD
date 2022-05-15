<?php
/**
 * @class Gruppi
 * @note Classe che gestisce i gruppi
 */
class Gruppi extends BaseClass
{

    protected
            $groups_active,
            $groups_max_jobs;

    protected function __construct()
    {
        parent::__construct();
        $this->groups_active = Functions::get_constant('GROUPS_ACTIVE');
        $this->groups_max_jobs = Functions::get_constant('GROUPS_MAX_JOBS');
    }

    public function activeGroups(){
        return $this->groups_active;
    }

    public function permissionManageGroups(){
        return Permissions::permission('MANAGE_GROUPS');
    }


    public function getGroup(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi WHERE id='{$id}' LIMIT 1");
    }

    public function getAllGroups(string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi WHERE 1 ",'result');
    }


    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     */
    public function listGroups(): string
    {
        $groups = $this->getAllGroups();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $groups);
    }

    public function ajaxGroupData(array $post):array{
        $id = Filters::int($post['id']);
        return $this->getGroup($id);
    }


    /** GESTIONE */

    /**
     * @fn NewGroup
     * @note Inserisce un gruppo
     * @param array $post
     * @return array
     */
    public function NewGroup(array $post): array
    {
        if ($this->permissionManageGroups()) {

            $nome = Filters::in($post['nome']);
            $tipo = Filters::int($post['tipo']);
            $img = Filters::in($post['immagine']);
            $url = Filters::in($post['url']);
            $statuto = Filters::in($post['statuto']);
            $visibile = Filters::checkbox($post['visibile']);


            DB::query("INSERT INTO gruppi (nome,tipo,immagine,url,statuto,visibile )  VALUES ('{$nome}','{$tipo}','{$img}','{$url}','{$statuto}','{$visibile}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo creato.',
                'swal_type' => 'success',
                'groups_list'=>$this->listGroups()
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
     * @fn ModGroup
     * @note Aggiorna un gruppo
     * @param array $post
     * @return array
     */
    public function ModGroup(array $post): array
    {
        if($this->permissionManageGroups()){
            $id=Filters::in( $post['id']);
            $nome = Filters::in($post['nome']);
            $tipo = Filters::int($post['tipo']);
            $img = Filters::in($post['immagine']);
            $url = Filters::in($post['url']);
            $statuto = Filters::in($post['statuto']);
            $visibile = Filters::checkbox($post['visibile']);


            DB::query("UPDATE  gruppi 
                SET nome = '{$nome}',tipo='{$tipo}', immagine='{$img}',url='{$url}',statuto='{$statuto}',visibile='{$visibile}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo modificato.',
                'swal_type' => 'success',
                'groups_list'=>$this->listGroups()
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
     * @fn DelGroup
     * @note Cancella un gruppo
     * @param array $post
     * @return array
     */
    public function DelGroup(array $post):array
    {
        if($this->permissionManageGroups()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo eliminato.',
                'swal_type' => 'success',
                'groups_list'=>$this->listGroups()
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