<div id="anagrafe">

    <div class="general_title">Anagrafe</div>

    <form class="ajax_form anagrafe_ajax" action="servizi/anagrafe/ajax.php" data-callback="searchSuccess" data-reset="false" data-swal="false">

        <div class="single_input">
            <div class="label">Parte del nome</div>
            <input type="text" name="name" value="">
        </div>

        <?php if ( Razze::getInstance()->activeRaces() ) { ?>
            <div class="single_input">
                <div class="label">Razza</div>
                <select name="race">
                    <?= Razze::getInstance()->listRaces(0, 'Tutte le razze') ?>
                </select>
            </div>
        <?php } ?>

        <div class="single_input">
            <div class="label">Sesso</div>
            <select name="gender">
                <?= Sessi::getInstance()->listGenders(0,'Tutti i sessi'); ?>
            </select>
        </div>

        <div class="single_input">
            <div class="label">Ordina per</div>
            <select name="order_by">
                <option value="name">Nome</option>
                <option value="race">Razza</option>
                <option value="gender">Sesso</option>
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


    <div class="fake-table anagrafe_table results_box">
    </div>
</div>

<script src="<?= Router::getPagesLink('servizi/anagrafe/index.js'); ?>"></script>
