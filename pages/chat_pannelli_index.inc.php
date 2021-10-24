<?php


require_once(__DIR__.'/../includes/required.php');

$pannello = gdrcd_filter('out',$_GET['pannello']);
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<?php


if(file_exists(__DIR__.'/chat/pannelli/'.$pannello.'.php')) {
    include(__DIR__ . '/chat/pannelli/' . $pannello . '.php');
}
else{
    echo 'Pannello non trovato.';
}

?>