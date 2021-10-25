<?php

require_once(__DIR__ . '/../abilita_class.php');

$abiReq = new Abilita();

if (isset($_POST['action'])) {

    switch ($_POST['action']) {
        case 'CreaAbiRequisito':
            $abiReq->NewAbiRequisito($_POST);
            echo "<div class='warning'>Requisito abilita' creato con successo.</div>";
            break;

        case 'ModAbiRequisito':
            $abiReq->ModAbiRequisito($_POST);
            echo "<div class='warning'>Requisito abilita' modificato con successo.</div>";
            break;

        case 'EliminaAbiRequisito':
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
    <div id="CreaAbiRequisito" class="gestione_form_container">

        <div class="gestione_form_title">Crea Requisito Abilita</div>

        <form method="POST" id="CreaAbiRequisitoForm" class="gestione_form">


            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abi" required>
                    <option value=""></option>
                    <?= $abiReq->ListaAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= ABI_LEVEL_CAP; $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- TIPO REQUISITO -->
            <div class="single_input">
                <div class="label">Tipo di requisito</div>
                <select name="tipo" required>
                    <option value=""></option>
                    <option value="<?= REQUISITO_ABI; ?>">Abilità</option>
                    <option value="<?= REQUISITO_STAT; ?>">Statistica</option>
                </select>
            </div>

            <!-- ID REQUISITO -->
            <div class="single_input">
                <div class="label">Requisito</div>
                <select name="id_req" required>
                    <option value=""></option>
                    <optgroup label="Abilita">
                        <?= $abiReq->ListaAbilita(); ?>
                    </optgroup>
                    <optgroup label="Caratteristiche">
                        <option value="0">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?></option>
                        <option value="1">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?></option>
                        <option value="2">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?></option>
                        <option value="3">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?></option>
                        <option value="4">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?></option>
                        <option value="5">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?></option>
                    </optgroup>
                </select>
            </div>

            <!-- LIVELLO REQUISITO -->
            <div class="single_input">
                <div class="label">Livello requisito</div>
                <select name="liv_req" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= ABI_LEVEL_CAP; $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <input type="hidden" name="action" value="CreaAbiRequisito" required><br>

            <input type="submit" class="InviaTools" value="Crea">

        </form>

    </div>

    <!-- MODIFICA -->
    <div id="ModAbiRequisito" class="gestione_form_container">

        <div class="gestione_form_title">Modifica Requisito Abilita</div>


        <form method="POST" id="ModAbiRequisitoForm" class="gestione_form">

            <!-- LISTA REQUISITI -->
            <div class="single_input">
                <div class="label">Seleziona Requisito</div>
                <select name="req_id" required>
                    <option value=""></option>
                    <?= $abiReq->ListaRequisiti(); ?>
                </select>
            </div>

            <!-- ABILITA -->
            <div class="single_input">
                <div class="label">Abilita</div>
                <select name="abi" required>
                    <option value=""></option>
                    <?= $abiReq->ListaAbilita(); ?>
                </select>
            </div>

            <!-- GRADO -->
            <div class="single_input">
                <div class="label">Grado</div>
                <select name="grado" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= ABI_LEVEL_CAP; $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- TIPO REQUISITO -->
            <div class="single_input">
                <div class="label">Tipo di requisito</div>
                <select name="tipo" required>
                    <option value=""></option>
                    <option value="<?= REQUISITO_ABI; ?>">Abilità</option>
                    <option value="<?= REQUISITO_STAT; ?>">Statistica</option>
                </select>
            </div>

            <!-- ID REQUISITO -->
            <div class="single_input">
                <div class="label">Requisito</div>
                <select name="id_req" required>
                    <option value=""></option>
                    <optgroup label="Abilita">
                        <?= $abiReq->ListaAbilita(); ?>
                    </optgroup>
                    <optgroup label="Caratteristiche">
                        <option value="0">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?></option>
                        <option value="1">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?></option>
                        <option value="2">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?></option>
                        <option value="3">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?></option>
                        <option value="4">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?></option>
                        <option value="5">
                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?></option>
                    </optgroup>
                </select>
            </div>

            <!-- LIVELLO REQUISITO -->
            <div class="single_input">
                <div class="label">Livello requisito</div>
                <select name="liv_req" required>
                    <option value=""></option>
                    <?php for ($i = 1; $i <= ABI_LEVEL_CAP; $i++) { ?>
                        <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php } ?>
                </select>
            </div>

            <input type="hidden" name="action" value="ModAbiRequisito" required><br>
            <input type="submit" class="InviaTools" value="Modifica">

        </form>

    </div>

    <!-- ELIMiNA -->
    <div id="EliminaAbiRequisito" class="gestione_form_container">

        <div class="gestione_form_title">Elimina Requisito Abilita</div>

        <form method="POST" id="EliminaAbiRequisitoForm" class="gestione_form">

            <!-- LISTA REQUISITI -->
            <div class="single_input">
                <div class="label">Seleziona Requisito</div>
                <select name="req_id" required>
                    <option value=""></option>
                    <?= $abiReq->ListaRequisiti(); ?>
                </select>
            </div>

            <input type="hidden" name="action" value="EliminaAbiRequisito"><br>
            <input type="submit" class="InviaTools" value="Elimina">
        </form>

    </div>

    <!-- JS per caricamento dati requisiti -->
    <script src="/pages/Abilita/Requisiti/JS/gestione_requisiti.js"></script>
</div>