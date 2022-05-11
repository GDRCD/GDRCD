<?php

require_once(__DIR__ . '/../../../includes/required.php');

$contatti = Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::in($_GET['pg']);
?>


<div class="fake-table contatti_list">
    <?php
   echo  $contatti->ContactList($id_pg);
    ?>

</div>
<?php
if($contatti->contactManage($id_pg))
    {?>
        <div class="tr footer">
            <a href="/main.php?page=scheda_contatti&op=new&id_pg=<?=$id_pg?>&pg=<?=$pg?>">Nuovo contatto</a>
        </div>
   <?php
    }
?>

<script src="/pages/scheda/contatti/JS/delete_contatti.js"></script>
