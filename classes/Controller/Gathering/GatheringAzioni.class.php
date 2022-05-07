<?php

/**
 * @class GatheringCategory
 * @note Classe per la gestione delle azioni
 * @required PHP 7.1+
 */
class GatheringAzioni extends Gathering
{
    public function  checkActionTime(){

        $time= DB::query("SELECT azioni_time FROM personaggio WHERE id ='{$this->me_id}'");
        var_dump($time);
    }




}