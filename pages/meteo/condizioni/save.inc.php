<?php

switch (Filters::out($_POST['op'])) {

    # Creazione Condizione
    case 'save_new':
        $nome = Filters::in( $_POST['nome']);
        $vento = implode(",",Filters::in($_POST['vento']));
        $img = Filters::in( $_POST['img']);
        MeteoCondizioni::getInstance()->newCondition($nome, $vento,$img);
        break;
        #Modifica condizione
    case 'save_edit':
        $nome = Filters::in( $_POST['nome']);
        $vento = implode(",",$_POST['vento']);
        $id=Filters::in( $_POST['id']);
        $img = Filters::in( $_POST['img']);
        MeteoCondizioni::getInstance()->editCondition($nome, $vento, $id,$img);
        break;
    # Delete condizione
    case 'delete':
        $id=Filters::in($_POST['id']);
        MeteoCondizioni::getInstance()->deleteCondition($id);
        break;


    default:
        die('Operazione non riconosciuta.');
}


echo '<div class="warning">' .Filters::out($MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_meteo_condizioni"><?php echo Filters::out(
            $MESSAGE['interface']['user']['link']['back'] ); ?></a>
</div>