<?php

require ROOT . 'plugins/Carbon/autoload.php';

# ! Non eliminare!
use Carbon\Carbon;
use Carbon\CarbonInterval;

class CarbonWrapper
{
    /**
     * @fn format
     * @note Formatta una data in base al formato passato
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function format(string $date, string $format = 'd/m/Y H:i:s'): string
    {
        return Carbon::parse($date)->format($format);
    }

    /**
     * @fn getNow
     * @note Ritorna la data e l'ora corrente
     * @param string $format
     * @return string
     */
    public static function getNow(string $format = 'Y-m-d H:i:s'): string
    {
        return Carbon::now()->format($format);
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
        $dt = Carbon::createFromFormat('Y-m-d', $date);
        return $dt->subDays($days)->format('Y-m-d H:i:s');
    }

    /**
     * @fn AddDays
     * @note Aggiunge un numero preciso di giorni a una data
     * @param string $date
     * @param int $days
     * @return string
     */
    public static function AddDays(string $date, int $days): string
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);
        return $dt->addDays($days)->format('Y-m-d H:i:s');
    }

    /**
     * @fn DatesDifferenceMonths
     * @note Calcola il numero di mesi di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceMonths(string $date1, string $date2): int
    {
        $start = Carbon::createFromFormat('Y-m-d', $date1);
        $end = Carbon::createFromFormat('Y-m-d', $date2);
        return $start->diffInMonths($end);
    }

    /**
     * @fn DatesDifferenceDays
     * @note Calcola il numero di giorni di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceDays(string $date1, string $date2): int
    {
        $start = Carbon::createFromFormat('Y-m-d', $date1);
        $end = Carbon::createFromFormat('Y-m-d', $date2);
        return $start->diffInDays($end);
    }

    /**
     * @fn DatesDifferenceHours
     * @note Calcola le ore di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceHours(string $date1, string $date2): int
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $date1);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $date2);
        return $start->diffInHours($end);
    }

    /**
     * @fn DatesDifferenceMinutes
     * @note Calcola i minuti di differenza tra due date
     * @param string $date1
     * @param string $date2
     * @return int
     */
    public static function DatesDifferenceMinutes(string $date1, string $date2): int
    {
        try {
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $date1);
            $end = Carbon::createFromFormat('Y-m-d H:i:s', $date2);
            return $start->diffInMinutes($end);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @fn greaterThan
     * @note Verifica se una data è maggiore di un'altra
     * @param string $greater
     * @param string $than
     * @return int
     */
    public static function greaterThan(string $greater, string $than): int
    {
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $greater);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $than);
        return $date1->greaterThan($date2);
    }

    /**
     * @fn lowerThan
     * @note Verifica se una data è minore di un'altra
     * @param string $lower
     * @param string $than
     * @return int
     */
    public static function lowerThan(string $lower, string $than): int
    {
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $lower);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $than);
        return $date1->lessThan($date2);
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
        if ( empty($last_exec) ) {
            return true;
        } else {
            // Altrimenti estraggo la differenza in base al tipo
            $diff = match ($interval_type) {
                'months' => CarbonWrapper::DatesDifferenceMonths(date('Y-m-d'), Filters::date($last_exec, 'Y-m-d')),
                'days' => CarbonWrapper::DatesDifferenceDays(date('Y-m-d'), Filters::date($last_exec, 'Y-m-d')),
                'hours' => CarbonWrapper::DatesDifferenceHours(date('Y-m-d H:i:s'), Filters::date($last_exec, 'Y-m-d H:i:s')),
                'minutes' => CarbonWrapper::DatesDifferenceMinutes(date('Y-m-d H:i:s'), Filters::date($last_exec, 'Y-m-d H:i:s')),
                default => 0,
            };

            // Controllo se è superato il timer richiesto
            return ($diff >= $interval);
        }
    }
}