<?php

require(__DIR__ . '/../../../core/required.php');

$gestione = Gestione::getInstance();

if ($gestione->constantsPermission()) {

    ?>


    <div class="gestione_pagina gestione_costanti">

        <div class="gestione_incipit">
            <div class="title"> Gestione Costanti</div>
            Questa sezione è utilizzata per la gestione delle costanti fondamentali per il funzionamento, l'attivazione
            o
            l'utilizzo di alcuni moduli.
        </div>

        <?php if (isset($resp)) { ?>
            <div class="warning">
                <?= $resp; ?>
            </div>
        <?php } ?>

        <div class="form_container">

            <form method="post" class="form">

                <?= $gestione->constantList(); ?>

                <div class="single_input">
                    <input type="hidden" name="action" value="save_constants">
                    <input type="submit" value="Salva">
                </div>

            </form>


        </div>


    </div>

    <script src="/pages/gestione/costanti/gestione_costanti_box.js"></script>
<?php } else {?>

    <div class="warning">Permesso negato.</div>

<?php } ?>
