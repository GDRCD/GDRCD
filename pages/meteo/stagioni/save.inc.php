<?php

switch ($_POST['op']) {

    # Creazione Condizione
    case 'save_new':
        $nome = Filters::in( $_POST['nome']);
        $minima = Filters::in( $_POST['minima']);
        $massima = Filters::in($_POST['massima']);
        $data_inizio =Filters::in( $_POST['data_inizio']);
        $alba= Filters::in($_POST['alba']);
        $tramonto = Filters::in( $_POST['tramonto']);
        MeteoStagioni::getInstance()->newSeason($nome, $minima, $massima, $data_inizio, $alba, $tramonto);
        break;
        #Modifica condizione
    case 'save_edit':
        $nome = Filters::in($_POST['nome']);
        $minima = Filters::in( $_POST['minima']);
        $massima = Filters::in( $_POST['massima']);
        $data_inizio = Filters::in($_POST['data_inizio']);
        $alba= Filters::in($_POST['alba']);
        $tramonto = Filters::in($_POST['tramonto']);

        $id = Filters::in( $_POST['id']);
        MeteoStagioni::getInstance()->editSeason($nome, $minima, $massima, $data_inizio, $alba, $tramonto, $id);
        break;
    # Delete condizione
    case 'delete':
        $id=Filters::in( $_POST['id']);
        MeteoStagioni::getInstance()->deleteSeason($id);
        break;
    case 'add_condition':
         $id_stagione= Filters::in($_POST['id']);
         $id_condizione= Filters::in($_POST['condizione']);
         $percentuale= Filters::in($_POST['percentuale']);
        MeteoStati::getInstance()->newClimaticState($id_stagione, $id_condizione, $percentuale);
        break;
    case 'delete_condition':
        $id=Filters::in($_POST['id']);
        MeteoStati::getInstance()->deleteClimaticState($id);
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