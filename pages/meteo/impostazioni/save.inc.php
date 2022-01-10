<?php

switch (Filters::out($_POST['op'])) {

    case 'save':
        $luna = Filters::in( $_POST['moon']);
        $vento = Filters::in( $_POST['wind']);
        $tipo = Filters::in( $_POST['type']);
        $api = Filters::in( $_POST['webapi_key']);
        $citta = Filters::in( $_POST['webapi_city']);
        $icone = Filters::in( $_POST['webapi_icon']);
        $formato=Filters::in( $_POST['webapi_format']);
        $time=Filters::in( $_POST['weather_time']);
        $class->saveSetting($luna, $vento,$tipo,$api,$citta,$icone,$formato, $time);
        break;
    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' .Filters::out($MESSAGE['warning']['done']) . '</div>';

?>