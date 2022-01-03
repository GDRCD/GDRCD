<?php

$abiReq = AbilitaRequisiti::getInstance();
$stat_cls = Statistiche::getInstance();

if (isset($_POST['action'])) {

    switch ($_POST['action']) {
        case 'op_insert':
            $abiReq->NewAbiRequisito($_POST);
            echo "<div class='warning'>Requisito abilita' creato con successo.</div>";
            break;

        case 'op_edit':
            $abiReq->ModAbiRequisito($_POST);
            echo "<div class='warning'>Requisito abilita' modificato con successo.</div>";
            break;

        case 'op_delete':
            $abiReq->DelAbiRequisito($_POST);
            echo "<div class='warning'>Requisito abilita' eliminato con successo.</div>";
            break;
    }
}

?>


<div class="gestione_pagina">

    <!-- INCIPIT -->
    <div class="gestione_incipit">
        <div class="title"> Gestione Requisiti Abilità</div>
        <br>
        Questa sezione serve ad inserire eventuali requisiti per l'acquisto di abilità. <br>
        <br>
        L'attivazione avviene tramite la voce <span class="highlight">"ABI_REQUIREMENT"</span> settata sul valore <span
                class="highlight">true</span>. La voce si trova nel file
        `/includes/constant_values.inc.php`.<br>
        <br>
        Il requisito viene applicato solo su un <span
                class="highlight">DETERMINATO LIVELLO DI UNA DETERMINATA ABILITA'</span>.<br><br>
        I requisiti sono di due tipi:
        <ul>
            <li>
                <div class="subtitle_gst">Requisito Abilità.</div>
                Serve selezionare quale livello di quale abilità è considerato come requisito.
            </li>
            <li>
                <div class="subtitle_gst">Requisito Statistica.</div>
                Serve selezionare quale valori di quale statistica è considerato come requisito.
            </li>
        </ul>
    </div>

    <!-- CREA -->
    <div id="CreaAbiRequisito" class="form_container">

        <div class="form_title">Crea Requisito Abilita</div>

        <form method="POST" id="CreaAbiRequisitoForm" class="form">


            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abilita" required>
                    <option value=""></option>
                    <?= $abiReq->listAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= $abiReq->abiLevelCap(); $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- TIPO REQUISITO -->
            <div class="single_input">
                <div class="label">Tipo di requisito</div>
                <select name="tipo" required>
                    <option value=""></option>
                    <?=$abiReq->listRequisitiType();?>
                </select>
            </div>

            <!-- ID REQUISITO -->
            <div class="single_input">
                <div class="label">Requisito</div>
                <select name="id_rif" required>
                    <option value=""></option>
                    <optgroup label="Abilita">
                        <?= $abiReq->listAbilita(); ?>
                    </optgroup>
                    <optgroup label="Caratteristiche">
                        <?=$stat_cls->listStats();?>
                    </optgroup>
                </select>
            </div>

            <!-- LIVELLO REQUISITO -->
            <div class="single_input">
                <div class="label">Livello requisito</div>
                <input type="number" name="lvl_rif" required>
            </div>

            <input type="hidden" name="action" value="op_insert" required><br>

            <input type="submit" class="InviaTools" value="Crea">

        </form>

    </div>

    <!-- MODIFICA -->
    <div id="ModAbiRequisito" class="form_container">

        <div class="form_title">Modifica Requisito Abilita</div>


        <form method="POST" id="ModAbiRequisitoForm" class="form edit-form">

            <!-- LISTA REQUISITI -->
            <div class="single_input">
                <div class="label">Seleziona Requisito</div>
                <select name="requisito" required>
                    <option value=""></option>
                    <?= $abiReq->listRequisiti(); ?>
                </select>
            </div>

            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abilita" required>
                    <option value=""></option>
                    <?= $abiReq->listAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= $abiReq->abiLevelCap(); $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- TIPO REQUISITO -->
            <div class="single_input">
                <div class="label">Tipo di requisito</div>
                <select name="tipo" required>
                    <option value=""></option>
                    <?=$abiReq->listRequisitiType();?>
                </select>
            </div>

            <!-- ID REQUISITO -->
            <div class="single_input">
                <div class="label">Requisito</div>
                <select name="id_rif" required>
                    <option value=""></option>
                    <optgroup label="Abilita">
                        <?= $abiReq->listAbilita(); ?>
                    </optgroup>
                    <optgroup label="Caratteristiche">
                        <?=$stat_cls->listStats();?>
                    </optgroup>
                </select>
            </div>

            <!-- LIVELLO REQUISITO -->
            <div class="single_input">
                <div class="label">Livello requisito</div>
                <input type="number" name="lvl_rif" required>
            </div>

            <input type="hidden" name="action" value="op_edit" required><br>
            <input type="submit" class="InviaTools" value="Modifica">

        </form>

    </div>

    <!-- ELIMiNA -->
    <div id="EliminaAbiRequisito" class="form_container">

        <div class="form_title">Elimina Requisito Abilita</div>

        <form method="POST" id="EliminaAbiRequisitoForm" class="form">

            <!-- LISTA REQUISITI -->
            <div class="single_input">
                <div class="label">Seleziona Requisito</div>
                <select name="requisito" required>
                    <option value=""></option>
                    <?= $abiReq->listRequisiti(); ?>
                </select>
            </div>

            <input type="hidden" name="action" value="op_delete"><br>
            <input type="submit" class="InviaTools" value="Elimina">
        </form>

    </div>

    <!-- JS per caricamento dati requisiti -->
    <script src="/pages/gestione/abilita/Requisiti/gestione_requisiti.js"></script>
</div>