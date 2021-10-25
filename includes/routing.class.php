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
     * @fn addRoutes
     * @note Get all php files whit routes in folder and sub-folders
     * @param string $routes_folder
     * @return void
     */
    public function addRoutes($array)
    {
        # Start container array for all routes
        $alldirs = [];

        foreach ($array as $path) {
            $file_path = $path['Path'];
            $ext = $path['Ext'];

            # If folder exist and is correct
            if (is_dir($file_path)) {

                # Extract all sub-folders and files in the folder
                $files = glob("{$file_path}/{$ext}"); //GLOB_MARK adds a slash to directories returned

                # Foreach extracted path
                foreach ($files as $file) {
                    # Get dir extension
                    $extention = pathinfo($file, PATHINFO_EXTENSION);

                    # If is a php file
                    if ($extention == 'php') {

                        # Add the path to the general paths array
                        array_push($alldirs, $file);
                    } else {

                        # Scan sub-folder and repeat process
                        $this->addRoutes($file);
                    }
                }
            }

        }



        # Foreach extracted dir
        foreach ($alldirs as $dir) {

            # Include extracted dir
            require_once($dir);
        }
    }
}