<div class="pagina_uffici">
    <div class="page_title">
        <h2>Menu utenti</h2>
    </div>
    <div class="page_body">
        <?=Menu::getInstance()->createMenu('Utenti'); ?>
    </div>
</div>


<script>
    $(function(){
        Menu();
    });
</script>