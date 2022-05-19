<?php

require ROOT.'plugins/Carbon/autoload.php';

use Carbon\Carbon;
use Carbon\CarbonInterval;

class CarbonWrapper{

    public function __construct()
    {

    }

    public static function SubtractDate($date,$days){
        $dt = Carbon::createFromFormat('Y-m-d',  $date);
        return $dt->subDays($days)->format('Y-m-d H:i:s');
    }


    public static function AddDate($date,$days){
       $dt = Carbon::createFromFormat('Y-m-d',  $date);
       return $dt->addDays($days)->format('Y-m-d H:i:s');
    }


}