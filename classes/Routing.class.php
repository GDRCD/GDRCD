<?php

/**
 * @class Router
 * @package Core
 * @note Router class for manage routing of the web page
 */
class Router
{
    /**
     * Init vars PUBLIC STATIC
     * @var Router $_instance ;
     */
    public static
        $_instance;

    /**
     * @fn getInstance
     * @note Self Instance
     * @return Router class
     */
    public static function getInstance(): Router
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return bool
     */
    public function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * @fn startClasses
     * @note Start loader for dynamic integrations of the classes
     * @return void
     */
    public static function startClasses()
    {
        spl_autoload_register([Router::getInstance(), 'loadController']);
    }

    /**
     * @fn loadController
     * @note Load called class
     * @param string $className
     * @throws Exception
     * @return void
     */
    private function loadController(string $className)
    {
        $originalName = $className;
        $className = __DIR__.'/../classes/' . $className . '.class.php';

        if(is_readable($className)) {
            require_once($className);
        }else{
            throw new Exception(
                "Class '$originalName' not exists.'");
        }
    }

}
