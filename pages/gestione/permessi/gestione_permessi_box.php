<?php

require(__DIR__.'/../../../includes/required.php');

$gestione = Gestione::getInstance();

?>

<div class="gestione_pagina gestione_permessi">

    <div class="gestione_incipit">
        <div class="title">Gestione permessi</div>
        Pagina per la creazione, modifica, eliminazione e gestione della gerarchia dei permessi.
    </div>

    <div class="gestione_form_container">

        <form method="POST" class="gestione_form">

            <ul id="permessi_gerarchia">
                <?=$gestione->permissionsListDrag();?>
            </ul>

            <div class="single_input">
                <button type="button" id="order_confirm">Conferma ordinamento.</button>
            </div>

        </form>



    </div>

</div>

<script src="/pages/gestione/permessi/gestione_permessi_box.js"></script>
