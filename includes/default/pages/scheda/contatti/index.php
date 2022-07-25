<?php
Router::loadRequired();

$scheda_con = SchedaContatti::getInstance();
$contatti = Contatti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$op = Filters::out($_GET['op']);

if ( $contatti->contactEnables() ) {
    if ( $scheda_con->isAccessible($id_pg) ) { ?>

        <div class="pagina_scheda_contatti">
            <div class="page_body">
                <?php require_once(__DIR__ . '/' . $contatti->loadManagementContactPage(Filters::out($op))); ?>
            </div>
        </div>

        <?php
    }
}
?>