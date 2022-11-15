<?php

date_default_timezone_set('Europe/Rome');

class Cronjob extends BaseClass
{
    /**
     * @fn inlineCronjob
     * @note Controlla se i Cronjob sono inline o via call
     * @return bool
     * @throws Throwable
     */
    public function inlineCronjob():bool
    {
        return Functions::get_constant('INLINE_CRONJOB');
    }

    /*** TABLE HELPERS ***/

    /**
     * @fn getCron
     * @note Ottieni i dati di un cronjob
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCron(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM cronjob WHERE id=:id LIMIT 1",[
            'id' => $id
        ]);
    }

    /**
     * @fn getAllCronjobs
     * @note Ottieni i dati di tutti i cronjob
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllCronjobs(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM cronjob WHERE 1", []);
    }

    /*** CONTROLS ***/

    /**
     * @fn isInExec
     * @note Controlla se un cron è gia' in esecuzione
     * @param int $id
     * @return bool
     * @throws Throwable
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
     * @throws Throwable
     */
    public function needExec(int $id): bool
    {
        $data = $this->getCron($id, 'last_exec,`interval`,interval_type');
        $interval = Filters::int($data['interval']);
        $last_exec = Filters::out($data['last_exec']);
        $interval_type = Filters::out($data['interval_type']);

        return CarbonWrapper::needExec($interval, $interval_type, $last_exec);
    }

    /**
     * @fn startExec
     * @note Esegue le operazioni prima del cron
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function startExec(int $id): void
    {
        DB::queryStmt("UPDATE cronjob SET in_exec=1 WHERE id=:id LIMIT 1",[
            'id' => $id
        ]);
    }

    /**
     * @fn endExec
     * @note Esegue le operazioni dopo il cron
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function endExec(int $id): void
    {
        DB::queryStmt("UPDATE cronjob SET in_exec=0,last_exec=NOW() WHERE id=:id LIMIT 1",[
            'id' => $id
        ]);
    }

    /*** FUNCTIONS ***/

    /**
     * @fn startCron
     * @note Esecuzione dei cronjob
     * @return void
     * @throws Throwable
     */
    public function startCron(): void
    {
        $cronjobs = $this->getAllCronjobs();

        foreach ( $cronjobs as $cronjob ) {
            $id = Filters::out($cronjob['id']);
            $name = Filters::out($cronjob['name']);
            $class = Filters::out($cronjob['class']);
            $function = Filters::out($cronjob['function']);

            if ( $this->needExec($id) && !$this->isInExec($id) ) {
                if ( method_exists($class, $function) ) {
                    $this->startExec($id);
                    $class::getInstance()->$function();
                    $this->endExec($id);
                } else {
                    die("La funzione <span style='color:red; font-weight: bold;'>{$function}</span> del Cronjob <span style='color:red; font-weight: bold;'>{$name}</span> non esiste.");
                }
            }
        }

    }
}
