<div class="pagina_gestione">
    <div class="page_title">
        <h2>Gestione</h2>
    </div>
    <div class="page_body">
        <?=Menu::getInstance()->createMenu('gestione'); ?>
    </div>
</div>

<script>
    $(function(){
       Menu();
    });
</script>