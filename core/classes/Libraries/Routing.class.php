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

    public static function getPageRedirect($page){
       return DB::query("SELECT redirect FROM pages WHERE page='{$page}' LIMIT 1");
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

    /**** DYNAMIC LOADING ***/

    /**
     * @fn loadPages
     * @note Carica una pagina in base all'engine scelto
     * @param string $page
     * @return void
     */
    public static function loadPages(string $page):void
    {

        global $MESSAGE;
        global $PARAMETERS;

        $db_search = Router::getPageRedirect($page);

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

    /**
     * @fn loadRequired
     * @note Carica il file required
     * @return void
     */
    public static function loadRequired():void{

        $root = dirname(__FILE__).'/../../';

        require_once($root.'/required.php');
    }

    /**** DYNAMIC LINK ****/

    /**
     * @fn getPagesLink
     * @note Ottieni il link di una pagina specifica da caricare in base all'engine
     * @param string $page
     * @return string
     */
    public static function getPagesLink(string $page):string
    {
        $db_search = Router::getPageRedirect($page);

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

        return 'Url Inexisent';

    }

    /**
     * @fn getCssLink
     * @note Ottieni il link per il css in base all'engine
     * @param string $page
     * @return string
     */
    public static function getCssLink(string $page): string
    {

        $db_search = Router::getPageRedirect($page);

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

    /**** DYNAMIC DIR PATH ****/

    /**
     * @fn getThemeDir
     * @note Ottiene il link relativo della cartella themes
     * @return string|void
     */
    public static function getThemeDir(){

        $engine = Functions::get_constant('STANDARD_ENGINE');

        if (file_exists(ROOT."includes/{$engine}/themes")) {
            return ROOT."includes/{$engine}/themes/";
        }


        if (file_exists(ROOT."includes/default/themes")) {
            return ROOT."includes/default/themes/";
        }

        die('Non esiste una cartella di default per i temi.');
    }

    /**
     * @fn getPagesDir
     * @note Ottieni il link relativo della cartella pages
     * @return string|void
     */
    public static function getPagesDir(){

        $engine = Functions::get_constant('STANDARD_ENGINE');

        if (file_exists(ROOT."includes/{$engine}/pages")) {
            return ROOT."includes/{$engine}/pages/";
        }


        if (file_exists(ROOT."includes/default/pages")) {
            return ROOT."includes/default/pages/";
        }

        die('Non esiste una cartella di default per i temi.');
    }

}
