<?php

abstract class BaseClass
{
    public static
        $_instance;

    protected string
        $me,
        $permission;

    protected array
        $parameters;

    protected int
        $me_id;

    protected function __construct()
    {
        global $PARAMETERS;
        $this->me = Filters::out($_SESSION['login']?? '');
        $this->me_id = Filters::int($_SESSION['login_id']?? '');
        $this->permission = Filters::int($_SESSION['permessi']?? '');
        $this->parameters = $PARAMETERS;
    }

    /**
     * @fn getInstance
     * @note Self Instance for static recall (BaseClass::__construct();)
     * @return static
     */
    public static function getInstance()
    {
        $className = get_called_class();

        if ( !($className::$_instance instanceof $className) ) {
            $className::$_instance = new $className();
        }

        return $className::$_instance;
    }
}
