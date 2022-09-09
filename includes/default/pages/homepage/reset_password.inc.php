<?php

/*
 * Procedura di recupero Password
 */
// Inizializzo le variabili dell'operazione
$feedback = '';

if ( !empty($_POST['email']) ) {
    // Ottengo TUTTI i Personaggi presenti in gioco
    $result = gdrcd_query("SELECT nome, email FROM personaggio", 'result');

    // Scorro i personaggi presenti in gioco
    while ( $row = gdrcd_query($result, 'fetch') ) {
        // Controllo la corrispondenza con la password
        if ( gdrcd_password_check($_POST['email'], $row['email']) ) {
            // Concludo il controllo in caso di corrispondenza
            gdrcd_query($result, 'free');

            // Genero una nuova password
            $pass = gdrcd_genera_pass();

            // Aggiorno il personaggio con la nuova password
            $hasReset = gdrcd_query("UPDATE personaggio SET pass = '" . gdrcd_encript($pass) . "' WHERE nome = '" . gdrcd_filter('in', $row['nome']) . "' LIMIT 1");

            // Se il reset della password è stata eseguita con successo, procedo con l'invio mail
            if ( $hasReset ) {
                // Inizializzo i parametri per l'invio della mail
                $subject = gdrcd_filter('out', $MESSAGE['register']['forms']['mail']['sub'] . ' ' . $PARAMETERS['info']['site_name']);
                $text = gdrcd_filter('out', $MESSAGE['register']['forms']['mail']['text'] . ': ' . $pass);

                // Tento l'invio della mail
                $hasEmailSent = mail($_POST['email'], $subject, $text, 'From: ' . $PARAMETERS['info']['webmaster_email']);
                // Aggiorno il feedback
                if ( $hasEmailSent ) {
                    $feedback = gdrcd_filter('out', $MESSAGE['warning']['modified']);
                }

            } // Altrimenti segnalo l'errore
            else {
                // Aggiorno il feedback
                $feedback = gdrcd_filter('out', $MESSAGE['warning']['cant_do']);
            }
        }
    }
}
/*
 * Fine Recupero Password
 */

?>
<strong>
    <?php echo gdrcd_filter('out', $MESSAGE['homepage']['forms']['forgot']); ?>
</strong>

<div class="pass_rec">

    <div class="form_container">
        <form class="form ajax_form" action="servizi/password/ajax.php">
            <div class="single_input">
                <div class="label">Email</div>
                <input type="text" name="email"/>
            </div>
            <div class="single_input">
                <input type="hidden" name="action" value="update_password_external"/>
                <input type="submit" value="Invia password"/>
            </div>
        </form>
    </div>

</div>