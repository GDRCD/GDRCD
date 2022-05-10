<?php

require_once(__DIR__ . '/../../../includes/required.php');

$contatti = Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);

$op = Filters::out($_GET['op']);
?>
<div class="gestione_incipit">
    Aggiunta di un nuovo contatto
</div>

<div class="form_container">
    <form method="POST" class="form">


        <div class="single_input">
            <div class='label'>
                Nome
            </div>
            <select name="nome">
                <?php
                $pg = Personaggio::getAllPG('id, nome', "id!={$id_pg}", 'ORDER BY nome');
                echo (Personaggio::listPG(0,$pg));
                ?>

            </select>
        </div>

<?php

