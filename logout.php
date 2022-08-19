<?php
const SESSION_LOCK = true;
require_once __DIR__ . '/core/required.php';

// leggo i dati della sessione prima di distruggerla
$data = Session::read();
Session::destroy();

// aggiorno l'orario di uscita del pg
DB::queryStmt(
    "UPDATE personaggio SET ora_uscita = NOW() WHERE nome = :username",
    ['username' => $data['login']]
);

?><!doctype html>
<html lang="it">
<head>
    <title>Logout</title>
    <meta http-equiv="Content-Type" content='text/html; charset=utf-8'>
    <link rel="stylesheet" href="themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" type='text/css'>
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<body class="logout_body">
<div class="logout_box">
    <span
        class="logout_text"><?php echo gdrcd_filter('out', $data['login']) . ' ' . $MESSAGE['logout']['confirmation']; ?></span>
    <span class="logout_text">
            <?php echo gdrcd_filter('out', $MESSAGE['logout']['logbackin']) . ' '; ?>
            <a href="index.php">
                <?php echo gdrcd_filter('out', $PARAMETERS['info']['homepage_name']); ?>
            </a>
        </span>
    <span class="logout_text"><?php echo gdrcd_filter('out', $MESSAGE['logout']['greeting']); ?></span>
</div>
</body>
</html>