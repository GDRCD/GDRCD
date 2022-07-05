<?php

Router::loadRequired();

$contatti_note=ContattiNote::GetInstance();
$id=Filters::in($_REQUEST['id']);

$nota=$contatti_note->getNota( $id,'titolo, nota');

echo "<h3>".Filters::html($nota['titolo'])."</h3>";

echo "<p>".Filters::html($nota['nota'])."</p>";