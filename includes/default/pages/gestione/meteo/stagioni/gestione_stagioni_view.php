<!-- Corpo della pagina -->
<div class="fake-table stagioni-table">
    <?=MeteoStagioni::getInstance()->seasonListManagement();?>
</div>

<script src="<?=Router::getPagesLink('gestione/meteo/stagioni/gestione_stagioni_view.js');?>"></script>