<?php

class BaseClass{

    /**
     * Init vars PUBLIC STATIC
     * @var Router $_instance ;
     */
    public static
        $_instance;

    public function __construct()
    {
    }

    /**
     * @fn getInstance
     * @note Self Instance for static recall (BaseClass::__construct();)
     * @return self
     */
    public static function getInstance():self
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}