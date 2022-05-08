<?php

switch ($_POST['op']) {

    # Creazione Condizione
    case 'save_new':
        MeteoStagioni::getInstance()->newSeason($_POST);
        break;
        #Modifica condizione
    case 'save_edit':
        MeteoStagioni::getInstance()->editSeason($_POST);
        break;
    # Delete condizione
    case 'delete':
        MeteoStagioni::getInstance()->deleteSeason($_POST);
        break;
    case 'add_condition':
        MeteoStati::getInstance()->newClimaticState($_POST);
        break;
    case 'delete_condition':
        MeteoStati::getInstance()->deleteClimaticState($_POST);
        break;
    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' . Filters::out( $MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_meteo_stagioni"  ><?php echo Filters::out(
            $MESSAGE['interface']['user']['link']['back'] ); ?></a>
</div>