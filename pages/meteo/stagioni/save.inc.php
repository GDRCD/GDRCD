<?php

switch ($_POST['op']) {

    # Creazione Condizione
    case 'save_new':
        $nome = gdrcd_filter('in', $_POST['nome']);
        $minima = gdrcd_filter('in', $_POST['minima']);
        $massima = gdrcd_filter('in', $_POST['massima']);
        $data_inizio = gdrcd_filter('in', $_POST['data_inizio']);
        $alba= gdrcd_filter('in', $_POST['alba']);
        $tramonto = gdrcd_filter('in', $_POST['tramonto']);
        $class->new($nome, $minima, $massima, $data_inizio, $alba, $tramonto);
        break;
        #Modifica condizione
    case 'save_edit':
        $nome = gdrcd_filter('in', $_POST['nome']);
        $minima = gdrcd_filter('in', $_POST['minima']);
        $massima = gdrcd_filter('in', $_POST['massima']);
        $data_inizio = gdrcd_filter('in', $_POST['data_inizio']);
        $alba= gdrcd_filter('in', $_POST['alba']);
        $tramonto = gdrcd_filter('in', $_POST['tramonto']);
        $id = gdrcd_filter('in', $_POST['id']);
        $class->edit($nome, $minima, $massima, $data_inizio, $alba, $tramonto, $id);
        break;
    # Delete condizione
    case 'delete':
        $id=gdrcd_filter('in', $_POST['id']);
        $class->delete($id);
        break;

    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_meteo_stagioni"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['user']['link']['back'] ); ?></a>
</div>