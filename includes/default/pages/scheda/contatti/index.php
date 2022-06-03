<?php
Router::loadRequired();

$scheda_con = SchedaContatti::getInstance();
$contatti=Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$op = Filters::out($_GET['op']);

if ($contatti->contatcEnables()) {
    if ($scheda_con->isAccessible($id_pg)) { ?>


    <div class="pagina_scheda pagina_scheda_contatti">

        <div class="general_title">Contatti</div>

        <?php require_once(__DIR__ . '/../menu.inc.php'); ?>

    </div>
    <div class="page_body">
        <?php require_once(__DIR__ . '/' . $contatti->loadManagementContactPage(Filters::out($op))); ?>
    </div>

<?php
    }
}
?>