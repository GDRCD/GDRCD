<?php
Router::loadRequired();

$contatti = Contatti::getInstance();
$contatti_note = ContattiNote::GetInstance();

$id_pg = Filters::int($_GET['id_pg']);//id della scheda pg
$pg = Filters::in($_REQUEST['pg']); // nome del pg
$id = Filters::int($_REQUEST['id']);//id del contatto

$id_contatto = $contatti->getContact($id, 'contatto, creato_il'); //id del personaggio del contatto

$contatto = Personaggio::getPgData($id_contatto['contatto']);//dati del personaggio del contattos

?>
<div class="fake-table">
    <div class="header">
        <?php
        echo $contatto['nome'] . " " . $contatto['cognome'] . " - Creato il: " . Filters::date($id_contatto['creato_il'], 'd/m/Y');
        ?>
    </div>
</div>

<div class="fake-table note_list">
    <?php
    echo $contatti_note->NoteList($id);
    ?>
</div>


<div class="fake-table">
    <div class="footer">
        <?php
        if (Personaggio::isMyPg($id_pg)) {

            ?>
            <a href="/main.php?page=scheda_contatti&id_pg=<?= $id_pg ?>&pg=<?= $pg ?>&op=new_nota&id=<?= $id ?>">Nuova
                nota</a> |
            <?php
        }
        ?>
        <a href="/main.php?page=scheda_contatti&id_pg=<?= $id_pg ?>&pg=<?= $pg ?>">Torna indietro</a>
    </div>
</div>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/delete_nota.js'); ?>"></script>
