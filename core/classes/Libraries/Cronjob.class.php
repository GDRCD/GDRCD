<?php

date_default_timezone_set('Europe/Rome');

class Cronjob extends BaseClass
{

    protected function __construct()
    {
        parent::__construct();
    }

    public  function inlineCronjob(){
        return Functions::get_constant('INLINE_CRONJOB');
    }

    /*** TABLE HELPERS ***/

    /**
     * @fn getCron
     * @note Ottieni i dati di un cronjob
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getCron(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM cronjob WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllCrons
     * @note Ottieni i dati di tutti i cronjob
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCrons(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM cronjob WHERE 1",'result');
    }

    /*** CONTROLS ***/

    /**
     * @fn isInExec
     * @note Controlla se un cron e' gia' in esecuzione
     * @param int $id
     * @return bool
     */
    public function isInExec(int $id): bool
    {
        $data = $this->getCron($id, 'in_exec');
        return Filters::bool($data['in_exec']);
    }

    /**
     * @fn needExec
     * @note Controlla se un cron necessita di esecuzione
     * @param int $id
     * @return bool
     */
    public function needExec(int $id): bool
    {
        $data = $this->getCron($id, 'last_exec,`interval`,interval_type');
        $interval = Filters::int($data['interval']);
        $last_exec = Filters::out($data['last_exec']);
        $interval_type = Filters::out($data['interval_type']);

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

    /**
     * @fn startExec
     * @note Esegue le operazioni prima del cron
     * @param int $id
     * @return void
     */
    public function startExec(int $id):void
    {
        DB::query("UPDATE cronjob SET in_exec=1 WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn endExec
     * @note Esegue le operazioni dopo il cron
     * @param int $id
     * @return void
     */
    public function endExec(int $id):void
    {
        DB::query("UPDATE cronjob SET in_exec=0,last_exec=NOW() WHERE id='{$id}' LIMIT 1");
    }

    /*** FUNCTIONS ***/

    /**
     * @fn startCron
     * @note Esecuzione dei cronjob
     * @return void
     */
    public function startCron()
    {

        $crons = $this->getAllCrons();

        foreach ($crons as $cron){
            $id = Filters::out($cron['id']);
            $name = Filters::out($cron['name']);
            $class = Filters::out($cron['class']);
            $function = Filters::out($cron['function']);

            if ($this->needExec($id) && !$this->isInExec($id)) {
                if(method_exists($class,$function)) {
                    $this->startExec($id);
                    $class::getInstance()->$function();
                    $this->endExec($id);
                }
                else{
                    die("La funzione <span style='color:red; font-weight: bold;'>{$function}</span> del Cronjob <span style='color:red; font-weight: bold;'>{$name}</span> non esiste.");
                }
            }
        }

    }
}
