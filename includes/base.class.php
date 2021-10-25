<?php

abstract class BaseClass
{

    /**
     * Init vars PUBLIC STATIC
     * @var Router $_instance ;
     */
    public static
        $_instance;
    /**
     * @var string $me
     * @var int $perm
     */
    protected $me,
        $permission;

    protected function __construct()
    {
        $this->me = gdrcd_filter('out', $_SESSION['login']);
        $this->permission = gdrcd_filter('num', $_SESSION['permessi']);
    }

    /**
     * @fn getInstance
     * @note Self Instance for static recall (BaseClass::__construct();)
     * @return mixed
     */
    public static function getInstance()
    {
        $className = get_called_class();

        if (!($className::$_instance instanceof $className)) {
            $className::$_instance = new $className();
        }

        return $className::$_instance;
    }
}