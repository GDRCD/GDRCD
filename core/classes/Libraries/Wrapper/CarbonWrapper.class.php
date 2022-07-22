<?php

require ROOT . 'plugins/Carbon/autoload.php';

use Carbon\Carbon;
use Carbon\CarbonInterval;

class CarbonWrapper{

    public function __construct()
    {

    }

    /**
     * @fn SubtractDays
     * @note Sottrae un numero specifico di giorni da una data
     * @param string $date
     * @param int $days
     * @return string
     */
    public static function SubtractDays(string $date, int $days): string
    {
        $dt = Carbon::createFromFormat('Y-m-d',  $date);
        return $dt->subDays($days)->format('Y-m-d H:i:s');
    }

    /**
     * @fn AddDays
     * @note Aggiunge un numero preciso di giorni ad una data
     * @param string $date
     * @param int $days
     * @return string
     */
    public static function AddDays(string $date, int $days): string
    {
       $dt = Carbon::createFromFormat('Y-m-d',  $date);
       return $dt->addDays($days)->format('Y-m-d H:i:s');
    }

    /**
     * @fn DatesDifferenceMonths
     * @note Calcola il numero di mesi di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceMonths(string $date1,string $date2): int
    {
       $start = Carbon::createFromFormat('Y-m-d',  $date1);
       $end = Carbon::createFromFormat('Y-m-d',  $date2);
       return $start->diffInMonths($end);
    }

    /**
     * @fn DatesDifferenceDays
     * @note Calcola il numero di giorni di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceDays(string $date1,string $date2): int
    {
       $start = Carbon::createFromFormat('Y-m-d',  $date1);
       $end = Carbon::createFromFormat('Y-m-d',  $date2);
       return $start->diffInDays($end);
    }

    /**
     * @fn DatesDifferenceHours
     * @note Calcola le ore di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceHours(string $date1,string $date2): int
    {
       $start = Carbon::createFromFormat('Y-m-d H:i:s',  $date1);
       $end = Carbon::createFromFormat('Y-m-d H:i:s',  $date2);
       return $start->diffInHours($end);
    }

    /**
     * @fn DatesDifferenceMinutes
     * @note Calcola i minuti di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceMinutes(string $date1,string $date2): int
    {
       $start = Carbon::createFromFormat('Y-m-d H:i:s',  $date1);
       $end = Carbon::createFromFormat('Y-m-d H:i:s',  $date2);
       return $start->diffInMinutes($end);
    }


    /**
     * @fn needExec
     * @note Controlla se una funzione necessita di esecuzione
     * @param int $interval
     * @param string $interval_type
     * @param string $last_exec
     * @return bool
     */
    public static function needExec(int $interval, string $interval_type, string $last_exec): bool
    {

        // Se non e' mai stato eseguito, lo eseguo
        if (empty($last_exec)) {
            return true;
        } else {
            // Altrimenti estraggo la differenza in base al tipo
            switch ($interval_type) {
                case 'months':
                    $diff = CarbonWrapper::DatesDifferenceMonths(date('Y-m-d'), Filters::date($last_exec,'Y-m-d'));
                    break;
                case 'days':
                    $diff = CarbonWrapper::DatesDifferenceDays(date('Y-m-d'), Filters::date($last_exec,'Y-m-d'));
                    break;
                case 'hours':
                    $diff = CarbonWrapper::DatesDifferenceHours(date('Y-m-d H:i:s'), Filters::date($last_exec,'Y-m-d H:i:s'));
                    break;
                case 'minutes':
                    $diff = CarbonWrapper::DatesDifferenceMinutes(date('Y-m-d H:i:s'), Filters::date($last_exec,'Y-m-d H:i:s'));
                    break;
                default:
                    $diff = 0;
                    break;
            }

            // Controllo se e' superato il timer richiesto
            return ($diff >= $interval);
        }
    }
}