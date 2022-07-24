<?php

$id_pg = Filters::int($_GET['id_pg']);

?>


<div class="scheda_transazioni_box">
    <div class="fake-table scheda_transazioni_table">
        <?=Personaggio::getInstance()->transactionsPage($id_pg);?>
    </div>
</div>

<div class="form_info">
    Mantenere il mouse sulla causale per poterne leggere tutto il testo.
</div>