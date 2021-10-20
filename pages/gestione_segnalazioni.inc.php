<?php


require_once(__DIR__.'/../includes/required.php');

$segn = gdrcd_filter('out',$_GET['segn']);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<?php


if(file_exists(__DIR__.'/gestione/segnalazioni/'.$segn.'.php')) {
    include(__DIR__ . '/gestione/segnalazioni/' . $segn . '.php');
}
else{
    echo 'Pagina non trovata.';
}

?>
