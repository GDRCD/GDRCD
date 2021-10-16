<?php
require_once('includes/required.php');

if(!defined("ENABLE_RESET") or ENABLE_RESET !== 1) {
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['doReset'])) {
    gdrcd_query("SET FOREIGN_KEY_CHECKS = 0");

    //Cancella tutte le tabelle
    $tablesR = gdrcd_query("SELECT table_name
FROM information_schema.tables
WHERE table_schema = '" . gdrcd_filter_in($PARAMETERS['database']['database_name']) . "'", 'result');
    
    while ($t = gdrcd_query($tablesR, "fetch")) {
        gdrcd_query("DROP TABLE IF EXISTS `" . $t['table_name']. "`");
    }
    
    gdrcd_query("SET FOREIGN_KEY_CHECKS = 1");
    
    //Chiama installer
    include(dirname(dirname(__FILE__)).'/installer.php');
    if(install_db()) {
        echo <<<HTML
<html>
<head>
<title>RESET GDRCD</title>
</head>
<body>
    <h1>Reset Land Completato</h1>
</body>
</html>
HTML;

    }
}
else {
    echo <<<HTML
<html>
<head>
    <title>RESET GDRCD</title>
</head>
<body>
    <h1>ATTENZIONE</h1>
    <h2>Da questa pagina è possibile resettare totalmente il database della land, riportandolo a una situazione
    vergine</h2>
    <p>Cliccando sul pulsante qui sotto <strong>tutte</strong> le tabelle nel Database verranno cancellate e verrà
    eseguito nuovamente l'installer principale di GDRCD</p>
    
    <form action="./reset.php" method="post" onsubmit="return confirm('Ma sicuro sicuro?');">
        <label>Sei veramente sicuro di voler procedere?</label>
        <br />
        <input type="submit" value="Sì, procedi" name="doReset"
            style="background: red; color: #FFF; font-weight: bold; border: 1px solid #fd3333;" />
        <input type="reset" value="No, annulla!"
            style="background: lightblue; color: #000; font-weight: bold; border: 1px solid #637980;">
    </form>
</body>
</html>
HTML;

}
