<?php

Router::loadRequired();

$prestavolto = SchedaPrestavolto::getInstance();

?>
<div id="prestavolto">
<?php if ( $prestavolto->PvActive() ) { ?>

    <div class="general_title">Prestavolto</div>

    <form class="ajax_form prestavolto_ajax" action="servizi/prestavolto/ajax.php" data-callback="searchSuccess" data-reset="false" data-swal="false">

        <div class="single_input">
            <div class="label">Parte del nome</div>
            <input type="text" name="name" value="">
        </div>
        <div class="single_input">
            <div class="label">Ordina per</div>
            <select name="order_by">
                <option value="name">Nome</option>
                <option value="surname">Cognome</option>
                
            </select>
        </div>
      
        <div class="single_input">
            <div class="label">Ordine</div>
            <select name="order_for">
                <option value="ASC">Crescente</option>
                <option value="DESC">Decrescente</option>
            </select>
        </div>

        <div class="single_input">
            <input type="hidden" name="action" value="search">
            <input type="submit" value="Cerca">
        </div>
    </form>


    <div class="fake-table prestavolto_table results_box">
    </div>
</div>

<script src="<?= Router::getPagesLink('servizi/prestavolto/index.js'); ?>"></script>
<?php } else { ?>

<div class="warning"> Funzione disabilitata.</div>

<div class="link_back"><a href="/main.php?page=servizi/index">Indietro</a></div>
<?php } ?>