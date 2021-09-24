<?php

require_once(dirname(__FILE__) . '/constant_values.inc.php');
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(dirname(__FILE__) . '/../vocabulary/' . $PARAMETERS['languages']['set'] . '.vocabulary.php');
require_once(dirname(__FILE__) . '/functions.inc.php');

if(!empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}