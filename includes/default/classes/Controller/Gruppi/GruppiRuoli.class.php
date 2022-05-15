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

    public function activeGroups(){
        return $this->groups_active;
    }


    public function getRole(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE id='{$id}' LIMIT 1");
    }

    public function getAllRoles(string $val = '*'){
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE 1",'result');
    }


}