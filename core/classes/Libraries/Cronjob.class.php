<?php

const METEO_UPDATE = 'meteo_update';
const STIPENDI = 'stipendi_assign';
date_default_timezone_set('Europe/Rome');

class Cronjob extends BaseClass
{

    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @fn getCron
     * @note Ottieni i dati di un cronjob
     * @param string $name
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getCron(string $name, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM cronjob WHERE name='{$name}' LIMIT 1");
    }

    /**
     * @fn isInExec
     * @note Controlla se un cron e' gia' in esecuzione
     * @param string $name
     * @return bool
     */
    public function isInExec(string $name): bool
    {
        $data = $this->getCron($name, 'in_exec');
        return Filters::bool($data['in_exec']);
    }

    /**
     * @fn needExecMinutes
     * @note Controlla se un cron necessita di esecuzione, controllo intervallo in minuti
     * @param string $name
     * @return bool
     */
    public function needExecMinutes(string $name): bool
    {
        $data = $this->getCron($name, 'last_exec,`interval`');
        $interval = Filters::int($data['interval']);
        $last_exec = Filters::out($data['last_exec']);

        if (empty($last_exec)) {
            DB::query("INSERT INTO cronjob(name,last_exec,in_exec) VALUES ('{$name}',NOW(),0,60)");
            return true;
        } else {
            $diff = CarbonWrapper::DatesDifferenceMinutes(date('Y-m-d H:i:s'), $last_exec);
            return ($diff >= $interval);
        }
    }

    /**
     * @fn needExecDays
     * @note Controlla se un cron necessita di esecuzione, controllo intervallo in giorni
     * @param string $name
     * @return bool
     */
    public function needExecDays(string $name): bool
    {
        $data = $this->getCron($name, 'last_exec,`interval`');
        $interval = Filters::int($data['interval']);
        $last_exec = Filters::out($data['last_exec']);

        if (empty($last_exec)) {
            DB::query("INSERT INTO cronjob(name,last_exec,in_exec) VALUES ('{$name}',NOW(),0,1)");
            return true;
        } else {
            $diff = CarbonWrapper::DatesDifferenceDays(date('Y-m-d H:i:s'), $last_exec);
            return ($diff >= $interval);
        }
    }

    /**
     * @fn startExec
     * @note Esegue le operazioni prima del cron
     * @param $name
     * @return void
     */
    public function startExec($name)
    {
        DB::query("UPDATE cronjob SET in_exec=1 WHERE name='{$name}' LIMIT 1");
    }

    /**
     * @fn endExec
     * @note Esegue le operazioni dopo il cron
     * @param $name
     * @return void
     */
    public function endExec($name)
    {
        DB::query("INSERT INTO cronjob(name,last_exec,in_exec) VALUES ('{$name}',NOW(),0)");
    }

    /**
     * @fn startCron
     * @note Esecuzione dei cronjob
     * @return void
     */
    public function startCron()
    {

        // AGGIORNAMENTO METEO GLOBALE
        if ($this->needExecMinutes(METEO_UPDATE) && !$this->isInExec(METEO_UPDATE)) {
            $this->startExec(METEO_UPDATE);
            Meteo::getInstance()->generateGlobalWeather();
            $this->endExec(METEO_UPDATE);
        }

        // ASSEGNAZIONE STIPENDI
        if ($this->needExecDays(STIPENDI) && !$this->isInExec(STIPENDI)) {
            $this->startExec(STIPENDI);
            Gruppi::getInstance()->cronSalaries();
            $this->endExec(STIPENDI);
        }


    }


}
