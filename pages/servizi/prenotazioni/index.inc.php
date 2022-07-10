<?php

// Avvio l'operazione di prenotazione stanza
if (gdrcd_filter('get', $_POST['action']) == "bookRoom") {

    // Recupero i valori del FORM
    $idRoom = gdrcd_filter('get', $_POST['id']);
    $timeRoom = gdrcd_filter('num', gdrcd_filter('get', $_POST['ore']));

    // Preparo un controllo preliminare per assicurarmi l'affitabilità della stanza
    $checkRoom = gdrcd_query("SELECT costo, privata, scadenza, proprietario FROM mappa WHERE id = " . gdrcd_filter('num', $idRoom ) . "  LIMIT 1");

    if(
            $checkRoom['privata'] == 1
        &&  $checkRoom['costo'] >= 0
        &&  $checkRoom['scadenza'] <= strftime('%Y-%m-%d %H:%M:%S')
    ) {
        $bookableRoom = true;
    } else {
        $bookableRoom = false;
    }

    // Se la stanza è affittabile, allora procedo con la prenotazione
    if ($bookableRoom) {
        // Controllo i soldi in possesso del personaggio
        $checkPG = gdrcd_query("SELECT soldi FROM personaggio WHERE nome ='".$_SESSION['login']."' LIMIT 1");

        // Imposto il valore minimo delle ore a 1
        $timeRoom = $timeRoom >= 0 ? $timeRoom : 1;

        // Controllo se il personaggio ha abbastanza soldi
        if($checkPG['soldi'] >= ($timeRoom * $checkRoom['costo'])) {
            /*Opero la prenotazione*/
            gdrcd_query("UPDATE mappa SET proprietario = '".$_SESSION['login']."', invitati='', ora_prenotazione=NOW(), scadenza=DATE_ADD(NOW(), INTERVAL ".gdrcd_filter('get', $_POST['ore'])." HOUR) WHERE id = ".gdrcd_filter('num', $idRoom)." and scadenza < NOW() LIMIT 1");
            gdrcd_query("UPDATE personaggio SET soldi = soldi - ".gdrcd_filter('num', $timeRoom * $checkRoom['costo'])." WHERE nome = '".$_SESSION['login']."' LIMIT 1");
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['ok']).'</div>';
        } else {
            echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['no_bucks']).'</div>';
        }
    }
    else {
        // Se l'utente è il proprietario della stanza, allora lo segnalo
        // Forzo questo messaggio per evitare che l'utente veda un messaggio di errore
        if($checkRoom['proprietario'] == $_SESSION['login']) {
            echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['hotel']['already_booked_by_user']).'</div>';
        }
        // Altrimenti la stranza è prenotata da un altro utente
        else {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['cant_do']).'</div>';
        }
    }
 }

// Ottengo le stanze private che l'utente può prenotare
$query = "SELECT mappa.id, mappa.nome AS luogo, mappa.costo, mappa.proprietario, mappa.scadenza, mappa_click.nome FROM mappa JOIN mappa_click on mappa.id_mappa = mappa_click.id_click WHERE mappa.privata = 1 ORDER BY mappa.nome, mappa.costo DESC";
$result = gdrcd_query($query, 'result');

// Scorro i risultati e inserisco le opzioni
$optionsRooms = [];
while ($rooms = gdrcd_query($result, 'fetch')) {
    $isSelected = gdrcd_filter('get', $_REQUEST['id']) == $rooms['id'] ? 'selected' : NULL;
    $isBooked = strtotime($rooms['scadenza']) > strtotime(date('Y-m-d H:m:s'));
    $optionsRooms[] = $isBooked
                        ? '<option value="' . $rooms['id'] . '" disabled>' . gdrcd_filter('out', $rooms['luogo'].', '.$rooms['nome']).' ('.$rooms['proprietario'].', '.gdrcd_format_time($rooms['scadenza']).') </option>'
                        : '<option value="' . $rooms['id'] . '" ' . $isSelected . '>' . gdrcd_filter('out', $rooms['luogo'].', '.$rooms['nome']).' ('.$rooms['costo'].' '.strtolower($PARAMETERS['names']['currency']['plur']).' '.$MESSAGE['interface']['hotel']['per_hour'].')</option>';
}

$optionsHours = [];
for($i = 1; $i <= 12; $i++) {
    $optionsHours[] = "<option value=".$i.">".$i." ".gdrcd_filter('out', $MESSAGE['interface']['hotel']['hours'])."</option>";
}

// Controllo su presenza stanze private e funzionalità sbloccata
if( ( is_array($optionsRooms) && count($optionsRooms) == 0 ) || ($PARAMETERS['mode']['privaterooms'] == 'OFF')) { ?>
    <div class="warning"><?=gdrcd_filter('out', $MESSAGE['interface']['hotel']['no_room']);?></div>
<?php } else { ?>
    <!-- FORM -->
    <div id="PrenotaStanza" class="servizi_form_container">

        <div class="servizi_form_title"><?= gdrcd_filter('out', $MESSAGE['interface']['hotel']['form']['bookRoom']['title']); ?></div>

        <form method="POST" id="PrenotaStanzaForm" class="servizi_form" action="main.php?page=servizi_prenotazioni">

            <!-- STANZE -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['hotel']['room']; ?></div>
                <select name="id">
                    <?php echo implode('', $optionsRooms); ?>
                </select>
            </div>

            <!-- ORE -->
            <div class="single_input">
                <div class="label"><?= $MESSAGE['interface']['hotel']['hours']; ?></div>
                <select name="ore">
                    <?php echo implode('', $optionsHours); ?>
                </select>
            </div>

            <!-- SUBMIT + EXTRA -->
            <div class="single_input split-50">
                <input type="hidden" name="action" value="bookRoom" required>
                <input type="submit" value="<?= gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>">
            </div>

        </form>

    </div>
<?php } ?>