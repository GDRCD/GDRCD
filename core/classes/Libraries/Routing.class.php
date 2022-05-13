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
     * @fn is_ajax
     * @note Controlla se la pagina viene richiamata o meno in ajax
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
        spl_autoload_register([Router::getInstance(), 'loadClasses']);
    }

    /**
     * @throws Exception
     */
    public final function loadClasses(string $className)
    {

        $exist = $this->loadLibraries($className);

        if (!$exist) {
            $this->loadController($className);
        }
    }

    /**
     * @fn loadController
     * @note Load called class
     * @param string $className
     * @return bool
     * @throws Exception
     */
    private final function loadLibraries(string $className): bool
    {

        $path = ROOT . '/core/classes';
        $roots = $this->dirList($path);

        $exist = false;

        foreach ($roots as $folder) {

            $new_path = $folder . '/' . $className . '.class.php';

            if (file_exists($new_path) && is_readable($new_path)) {
                require_once($new_path);
                $exist = true;
                break;
            }
        }
        return $exist;
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
        $path = ROOT . '/includes/default/classes';
        $roots = $this->dirList($path);

        $exist = false;

        foreach ($roots as $folder) {

            $new_path = $folder . '/' . $className . '.class.php';

            if (file_exists($new_path) && is_readable($new_path)) {
                require_once($new_path);
                $exist = true;
                break;
            }
        }

        if (!$exist) {
            throw new Exception(
                "Class '$className' not exists.'");
        }

    }

    /**
     * @fn dirList
     * @note Estrae la lista delle directory di una cartella
     * @param string $dir
     * @param array $results
     * @return array
     */
    private final function dirList(string $dir, array &$results = array()): array
    {

        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (is_dir($path) && ($value != ".") && ($value != "..")) {
                $this->dirList($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

    public static function loadPages($page)
    {

        global $MESSAGE;
        global $PARAMETERS;

        $db_search = DB::query("SELECT redirect FROM pages WHERE page='{$page}' LIMIT 1");

        if (!empty($db_search['redirect'])) {
            if (file_exists($db_search['redirect'])) {
                require_once($db_search['redirect']);
            }
        }


        $engine = Functions::get_constant('STANDARD_ENGINE');
        if (file_exists(ROOT."includes/{$engine}/pages/{$page}")) {
            require_once(ROOT."includes/{$engine}/pages/{$page}");
        }

        if (file_exists(ROOT."includes/default/pages/{$page}")) {
            require_once(ROOT."includes/default/pages/{$page}");
        }


    }

    public static function getPagesLink($page)
    {
        $db_search = DB::query("SELECT redirect FROM pages WHERE page='{$page}' LIMIT 1");

        if (!empty($db_search['redirect'])) {
            if (file_exists($db_search['redirect'])) {
                return $db_search['redirect'];
            }
        }


        $engine = Functions::get_constant('STANDARD_ENGINE');
        if (file_exists("includes/{$engine}/pages/{$page}")) {
            return "includes/{$engine}/pages/{$page}";
        }

        if (file_exists("includes/default/pages/{$page}")) {
            return "includes/default/pages/{$page}";
        }

        return '';

    }

    public static function loadCss(string $page): string
    {

        $db_search = DB::query("SELECT redirect FROM pages WHERE page='{$page}' LIMIT 1");


        if (!empty($db_search['redirect'])) {
            if (file_exists($db_search['redirect'])) {
                return $db_search['redirect'];
            }
        }

        $engine = Functions::get_constant('STANDARD_ENGINE');

        if (file_exists("includes/{$engine}/themes/{$page}")) {
            return "includes/{$engine}/themes/{$page}";
        }


        if (file_exists("includes/default/themes/{$page}")) {
            return "includes/default/themes/{$page}";
        }

        return '';
    }

    public static function loadRequired(){

        $root = dirname(__FILE__).'/../../';

        require_once($root.'/required.php');
    }

    public static function getThemeDir(){

        $engine = Functions::get_constant('STANDARD_ENGINE');

        if (file_exists("includes/{$engine}/themes")) {
            return "includes/{$engine}/themes/";
        }


        if (file_exists("includes/default/themes")) {
            return "includes/default/themes/";
        }

        die('Non esiste una cartella di default per i temi.');
    }


}
