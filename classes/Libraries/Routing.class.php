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
    public static final function getInstance(): Router
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return bool
     */
    public static final function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * @fn startClasses
     * @note Start loader for dynamic integrations of the classes
     * @return void
     */
    public static final function startClasses()
    {
        spl_autoload_register([Router::getInstance(), 'loadController']);
    }

    /**
     * @fn loadController
     * @note Load called class
     * @param string $className
     * @return void
     * @throws Exception
     */
    private final function loadController(string $className)
    {
        $path = ROOT.'/classes' ;
        $roots = $this->dirList($path);

        $exist = false;

        foreach ($roots as $folder) {

            $new_path = $folder.'/'.$className.'.class.php';

            if (file_exists($new_path) && is_readable($new_path)) {
                require_once($new_path);
                $exist = true;
                break;
            }
        }

        if(!$exist){
            throw new Exception(
                "Class '$className' not exists.'");
        }

    }

    private final function dirList($dir, &$results = array()) {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if(is_dir($path) && ($value != ".") && ($value != "..")) {
                $this->dirList($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }


}
