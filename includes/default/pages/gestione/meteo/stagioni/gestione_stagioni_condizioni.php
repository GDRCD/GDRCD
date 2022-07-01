<?php


$stagione_id = Filters::int($_REQUEST['id']);
$stagione_data = MeteoStagioni::getInstance()->getSeason($stagione_id);
$stagione_name = Filters::out($stagione_data['nome']);

?>


<div class="gestione_stagioni_condizioni form_container">


    <div class="general_incipit">
        <div class="title"> Associazione condizioni a stagione</div>
        <br>
        <div class="subtitle">Pagina per associazione delle condizioni ad una stagione</div>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Associare una condizione meteo ad una stagione</li>
            <li>Dissociare una condizione meteo da una stagione</li>
            <li>Definire le percentuali di una o l'altra condizione. Per sovrascrivere la vecchia percentuale basta
                associare nuovamente una stagione con una condizione ed il valore verra' sovrascritto.
            </li>
        </ul>
    </div>


    <div class="fake-table stagioni-condizioni-table">
        <?= MeteoStagioni::getInstance()->seasonConditionsManageList($stagione_id); ?>
    </div>

    <form class="form ajax_form"
          action="gestione/meteo/stagioni/gestione_stagioni_ajax.php"
          data-callback="refreshStagioniTable">

        <div class="form_title">Aggiungi condizione a stagione "<?= $stagione_name; ?>"</div>

        <div class="single_input">
            <div class="label">Condizione</div>
            <select name="condizione" id="condizione" required>
                <?= MeteoCondizioni::getInstance()->listConditions(); ?>
            </select>
        </div>
        <div class="single_input">
            <div class="label">Percentuale</div>
            <input type="number" name="percentuale" id="percentuale" class="form_input">
        </div>

        <div class="single_input">
            <input type="submit" name="submit" value="Assegna"/>
            <input type="hidden" name="action" value="op_assign_condition">
            <input type="hidden" name="id" value="<?= $stagione_id; ?>">
        </div>
    </form>

    <form class="form  ajax_form"
          action="gestione/meteo/stagioni/gestione_stagioni_ajax.php"
          data-callback="refreshStagioniTable">

        <div class="form_title">Rimuovi condizione a stagione "<?= $stagione_name; ?>"</div>

        <div class="single_input">
            <div class="label">Condizione</div>
            <select name="condizione" id="condizione" required>
                <?= MeteoCondizioni::getInstance()->listConditions(); ?>
            </select>
        </div>

        <div class="single_input">
            <input type="submit" name="submit" value="Rimuovi"/>
            <input type="hidden" name="action" value="op_remove_condition">
            <input type="hidden" name="id" value="<?= $stagione_id; ?>">
        </div>
    </form>

    <div class="link_back">
        <a href="main.php?page=gestione/meteo/stagioni/gestione_stagioni_index">Torna indietro</a>
    </div>
</div>

<script src="<?= Router::getPagesLink('gestione/meteo/stagioni/gestione_stagioni_condizioni.js'); ?>"></script>