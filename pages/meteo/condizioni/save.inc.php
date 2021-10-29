<?php

switch (Filters::out($_POST['op'])) {

    # Creazione Condizione
    case 'save_new':
        $nome = Filters::in( $_POST['nome']);
        $vento = implode(",",Filters::in($_POST['vento']));
        $img = Filters::in( $_POST['img']);
        $class->new($nome, $vento,$img);
        break;
        #Modifica condizione
    case 'save_edit':
        $nome = gdrcd_filter('in', $_POST['nome']);
        $vento = implode(",",$_POST['vento']);
        $id=gdrcd_filter('in', $_POST['id']);
        $img = gdrcd_filter('in', $_POST['img']);
        $class->edit($nome, $vento, $id,$img);
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
    <a href="main.php?page=gestione_meteo_condizioni"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['user']['link']['back'] ); ?></a>
</div>