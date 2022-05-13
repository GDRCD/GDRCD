<?php

$pg = Filters::in($_REQUEST['pg']);
$id_pg = Filters::in($_REQUEST['id_pg']);
$id_abilita = Filters::int($_REQUEST['abilita']);
$pg_class = PersonaggioAbilita::getInstance();

$resp = $pg_class->downgradeAbilita($id_abilita, $id_pg);


# Cambio pagina per evitare doppio invio
?>


    <div class="warning"> <?= $resp['mex']; ?></div>
    <div class="link_back">
        <a href="main.php?page=scheda_skill&id_pg=<?= $id_pg; ?>&pg=<?=$pg;?>">Indietro</a>
    </div>

<?php

Functions::redirect("/main.php?page=scheda_skill&pg={$pg}&id_pg={$id_pg}",3);