<?php
require_once(__DIR__ . '/../../../includes/required.php');

$contatti = Contacts::getInstance();
$contatti_note=ContactsNotes::GetInstance();

$id_pg = Filters::int($_GET['id_pg']);//id della scheda pg
$pg = Filters::in($_REQUEST['pg']); // nome del pg
$id=Filters::int($_REQUEST['id']);//id del contatto

$id_contatto=$contatti->getContact('contatto, creato_il', $id); //id del personaggio del contatto

$contatto=Personaggio::getPgData($id_contatto['contatto']);//dati del personaggio del contattos

?>
<div class="fake-table">
    <div class="header">
        <?php
        echo $contatto['nome'] ." ".$contatto['cognome']. " - Creato il: ". Filters::date( $id_contatto['creato_il'],'d/m/Y');
        ?>
    </div>
</div>

<div class="fake-table note_list">
    <?php
    echo  $contatti_note->NoteList($id);
    ?>

</div>

<?php
        $all_note=$contatti_note->getAllNote($id, $id_pg);
            foreach ($all_note as $nota){
                echo "<div class='sub-tr'>". $nota['nota']."</div>";
                if($contatti->contactManage($id_pg))
                {?>
                <div class='sub-tr'>
                    Creato il: <?=Filters::date($nota['creato_il'],'d/m/Y')?> | Creato da: <?=Personaggio::nameFromId($nota['creato_da'])?> |
                    <a href='/main.php?page=scheda_contatti&op=edit_nota&id=<?=$id?>&id_pg=<?=$id_pg?>&pg=<?=$pg?>' title='Modifica'><i class='fas fa-edit'></i></a> |
                    <a class='ajax_link' data-id='<?=$id?>' data-action='delete_nota' href='#' title='Elimina'><i class='far fa-trash'></i></a>
                </div>

            <?php
                }
                echo "<hr>";
            }


        ?>
        </div>
    </div>


    <div class="footer">
        <a href="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>&op=new_nota&id=<?=$id?>">Nuova nota</a> |
        <a href="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>">Torna indietro</a>
    </div>
</div>

<script src="/pages/scheda/contatti/JS/delete_nota.js"></script>
