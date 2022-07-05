<?php
Router::loadRequired();

$contatti = Contatti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::out($_REQUEST['pg']);


$op = Filters::out($_GET['op']);
?>
<div class="gestione_incipit">
    Aggiunta di un nuovo contatto
</div>

<div class="form_container">
    <form class="form ajax_form"
          action="scheda/contatti/contact_ajax.php"
          data-reset="false"
          data-callback="goBackContatti">

        <div class="single_input">
            <div class='label'>
                Nome
            </div>
            <select name="contatto" required>
                <?php
                $contattiPresenti=$contatti->contattiPresenti($id_pg);
                $lista = Personaggio::getInstance()->getAllPG('id, nome', "id NOT IN ({$contattiPresenti})", 'ORDER BY nome');
                echo Personaggio::getInstance()->listPG(0,$lista);
                ?>

            </select>
        </div>
        <div class="single_input">
            <div class='label'>
                Categoria
            </div>
            <select name="categoria">
                <?php
                    echo $contatti->listContactCategories();
                ?>
            </select>
        </div>
        <div class="fake-table">
            <div class="header">
                <!-- bottoni -->
                <div class="single_input">
                    <div class='form_submit'>
                        <input type="hidden" name="action" value="contact_new">
                        <input type="hidden" id="id_pg" name="id_pg" value="<?=$id_pg?>">
                        <input type="hidden" id="pg" name="pg" value="<?=$pg?>">
                        <input type="hidden" id="url" value="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>">
                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/> |
                       <a href="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>">Torna indietro</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



<script src="<?= Router::getPagesLink('scheda/contatti/JS/contact_new.js'); ?>"></script>
