<?php
Router::loadRequired();

$contatti = Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::in($_GET['pg']);
if($contatti->contatcEnables()){
?>


<div class="fake-table contatti_list">
    <?php
   echo  $contatti->ContactList($id_pg);
    ?>

</div>

<div class="fake-table">
    <div class="footer"><?php
        if($contatti->contactUpdate($id_pg))
        {
            ?>
        <a href="/main.php?page=scheda_contatti&op=new&id_pg=<?=$id_pg?>&pg=<?=$pg?>">Nuovo contatto</a> |
            <?php
        }
        ?>
        <a href="/main.php?page=scheda&id_pg=<?=$id_pg?>&pg=<?=$pg?>">Torna indietro</a>
    </div>
</div>


    <script src="<?= Router::getPagesLink('scheda/contatti/JS/delete_contatti.js'); ?>"></script>
<?php
}
?>