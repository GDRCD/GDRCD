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
        $class->new($nome, $minima, $massima, $data_inizio, $alba, $tramonto);
        break;
        #Modifica condizione
    case 'save_edit':
        $nome = Filters::in($_POST['nome']);
        $minima = Filters::in( $_POST['minima']);
        $massima = Filters::in( $_POST['massima']);
        $data_inizio = Filters::in($_POST['data_inizio']);
        $alba= Filters::in($_POST['alba']);
        $tramonto = Filters::in($_POST['tramonto']);
        $condizioni = implode(",",Filters::in($_POST['condizioni']));
        $id = Filters::in( $_POST['id']);
        $class->edit($nome, $minima, $massima, $data_inizio, $alba, $tramonto, $id, $condizioni);
        break;
    # Delete condizione
    case 'delete':
        $id=Filters::in( $_POST['id']);
        $class->delete($id);
        break;

    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' . Filters::out( $MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_meteo_stagioni"><?php echo Filters::out(
            $MESSAGE['interface']['user']['link']['back'] ); ?></a>
</div>