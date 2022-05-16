<?php
require_once(__DIR__ . '/../../../includes/required.php');

$contatti_note=ContactsNotes::GetInstance();
$id=Filters::in($_REQUEST['id']);

$nota=$contatti_note->getNota('titolo, nota', $id);

echo "<h3>".Filters::html($nota['titolo'])."</h3>";

echo "<p>".Filters::html($nota['nota'])."</p>";