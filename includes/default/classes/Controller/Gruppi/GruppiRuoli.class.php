<?php
/**
 * @class GruppiRuoli
 * @note Classe che gestisce i ruoli dei gruppi
 */
class GruppiRuoli extends Gruppi
{

    protected $groups_max_roles;


    protected function __construct()
    {
        parent::__construct();
        $this->groups_max_roles = Functions::get_constant('GROUPS_MAX_ROLES');
    }

    public function permissionManageRoles(){
        return Permissions::permission('MANAGE_GROUPS');
    }

    public function getRole(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE id='{$id}' LIMIT 1");
    }

    public function getAllRoles(string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE 1",'result');
    }


    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     */
    public function listRoles(): string
    {
        $roles = $this->getAllRoles();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }

    public function ajaxRoleData(array $post):array{
        $id = Filters::int($post['id']);
        return $this->getRole($id);
    }


    /** GESTIONE */

    /**
     * @fn NewRole
     * @note Inserisce un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function NewRole(array $post): array
    {
        if ($this->permissionManageRoles()) {

            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);


            DB::query("INSERT INTO gruppi_ruoli (gruppo,nome,immagine,stipendio,poteri )  
                        VALUES ('{$group}','{$nome}','{$img}','{$stipendio}','{$poteri}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo creato.',
                'swal_type' => 'success',
                'roles_list'=>$this->listRoles()
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
    public function ModRole(array $post): array
    {
        if($this->permissionManageRoles()){
            $id=Filters::in( $post['id']);
            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);


            DB::query("UPDATE  gruppi_ruoli 
                SET gruppo='{$group}',nome = '{$nome}', 
                    immagine='{$img}',stipendio='{$stipendio}',
                    poteri='{$poteri}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo modificato.',
                'swal_type' => 'success',
                'roles_list'=>$this->listRoles()
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
    public function DelRole(array $post):array
    {
        if($this->permissionManageRoles()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi_ruoli WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo eliminato.',
                'swal_type' => 'success',
                'roles_list'=>$this->listRoles()
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