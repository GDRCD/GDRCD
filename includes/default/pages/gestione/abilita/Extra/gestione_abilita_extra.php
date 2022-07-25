<?php

# Inizializzazione classe necessaria
$abi_class = AbilitaExtra::getInstance();

?>

<div class="gestione_pagina gestione_abilita_extra">

    <div class="gestione_incipit">
        <div class="title"> Gestione Dati Extra Abilità</div>
        <br>
        Questa sezione serve ad inserire i dati extra di un'abilità, differenziate per livello. <br>
        <br>
        L'attivazione avviene tramite la voce <span class="highlight">"ABI_EXTRA"</span> settata sul valore <span
            class="highlight">true</span>. La voce si trova nel file
        `/includes/constant_values.inc.php`.<br>
        <br>
        Le informazioni attualmente modificabili sono:<br>
        <ul>
            <li>
                <div class="subtitle_gst">Descrizione per il singolo livello.</div>
                Il default per la descrizione si trova nella tabella `abilita` nella voce `descrizione`.
            </li>
            <li>
                <div class="subtitle_gst">Costo per il singolo livello.</div>
                Il default per il costo di un'abilità si trova nel file `/includes/constant_values.inc.php` sotto la
                voce
                <span class="highlight">"DEFAULT_ABI_PRICE"</span>. <br>
                Nel caso il costo di un livello <u><b>NON</b></u> sia compilato o sia compilato a "0", questo verra'
                calcolato secondo la formula <span class="highlight">(Livello x "DEFAULT_ABI_PRICE")</span>
            </li>
        </ul>
    </div>

    <!-- CREA FORM -->
    <div id="CreaAbiExtra" class="form_container">

        <div class="general_title">Crea Dati Livello Abilita</div>

        <form method="POST" id="CreaAbiExtraForm" class="form ajax_form"
              action="gestione/abilita/Extra/gestione_abilita_ajax.php">

            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abilita" required>
                    <option value=""></option>
                    <?= $abi_class->listAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ( $i = 1; $i <= $abi_class->abiLevelCap(); $i++ ) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- DESCRIZIONE -->
            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descr"></textarea>
            </div>

            <!-- COSTO -->
            <div class="single_input">
                <div class="label">Costo</div>
                <input type="number" name="costo">
            </div>


            <!-- SUBMIT + EXTRA -->
            <div class="single_input">
                <input type="hidden" name="action" value="op_insert" required><br>
                <input type="submit" class="InviaTools" value="Crea">
            </div>

        </form>
    </div>
    <!-- FINE CREA-->

    <!-- MODIFICA -->
    <div id="ModAbiExtra" class="form_container">

        <div class="general_title">Modifica Dati Livello Abilita</div>


        <form method="POST" id="ModAbiExtraForm" class="form edit-form ajax_form"
              action="gestione/abilita/Extra/gestione_abilita_ajax.php">

            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abilita" required>
                    <option value=""></option>
                    <?= $abi_class->listAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ( $i = 1; $i <= $abi_class->abiLevelCap(); $i++ ) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- TASTO ESTRAZIONE -->
            <div class="single_input">
                <button id="fake-extraction" type="button">Estrai dati</button>
            </div>

            <!-- DESCRIZIONE -->
            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descr"></textarea>
            </div>

            <!-- COSTO -->
            <div class="single_input">
                <div class="label">Costo</div>
                <input type="number" name="costo">
            </div>

            <!-- SUBMIT + EXTRA -->
            <div class="single_input">
                <input type="hidden" name="action" value="op_edit" required><br>
                <input type="submit" class="InviaTools" value="Modifica">
            </div>

        </form>
    </div>
    <!-- FINE MODIFICA -->


    <!-- ELIMINA -->
    <div id="EliminaAbiExtra" class="form_container">

        <div class="general_title">Elimina Dati Livello Abilita</div>

        <form method="POST" id="EliminaAbiExtraForm" class="form ajax_form"
              action="gestione/abilita/Extra/gestione_abilita_ajax.php">


            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abilita" required>
                    <option value=""></option>
                    <?= $abi_class->listAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ( $i = 1; $i <= $abi_class->abiLevelCap(); $i++ ) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>


            <!-- SUBMIT + EXTRA -->
            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"><br>
                <input type="submit" class="InviaTools" value="Elimina">
            </div>

        </form>
    </div>

</div>

<!-- JS PAGINA GESTIONE -->
<script src="<?= Router::getPagesLink('gestione/abilita/Extra/gestione_abilita_extra.js'); ?>"></script>