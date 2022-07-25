<?php

Router::loadRequired();

$contatti_note = ContattiNote::GetInstance();
$id = Filters::in($_GET['id']);
$id_pg = Filters::in($_GET['id_pg']);

$nota = $contatti_note->getNota($id);
$id_contatto = Filters::int($nota['id_contatto']);
?>

<div class="scheda_nota_details">
    <h3><?= Filters::html($nota['titolo']); ?></h3>
    <p><?= Filters::html($nota['nota']); ?></p>
</div>


<div class="link_back">
    <a href="/main.php?page=scheda/index&id_pg=<?= $id_pg ?>&op=contatti_view&id=<?= $id_contatto ?>">Torna indietro</a>
</div>