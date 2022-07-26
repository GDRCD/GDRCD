<?php

/*
 * Procedura di recupero Password
 */
// Inizializzo le variabili dell'operazione
$feedback = '';

if ( ! empty($_POST['email'])) {
    // Ottengo TUTTI i Personaggi presenti in gioco
    $result = gdrcd_query("SELECT nome, email FROM personaggio", 'result');

    // Scorro i personaggi presenti in gioco
    while($row = gdrcd_query($result, 'assoc')) {
        // Controllo la corrispondenza con la password
        if (gdrcd_password_check($_POST['email'], $row['email'])) {
            $pass = gdrcd_genera_pass();
            // Aggiorno il personaggio con la nuova password
            $hasReset = gdrcd_query("UPDATE personaggio SET pass = '" . gdrcd_encript($pass) . "' WHERE nome = '" .gdrcd_filter('in', $row['nome']). "' LIMIT 1");

            // Se il reset della password è stata eseguita con successo, procedo con l'invio mail
            if($hasReset) {
                // Inizializzo i parametri per l'invio della mail
                $subject = gdrcd_filter('out',$MESSAGE['register']['forms']['mail']['sub'] . ' ' . $PARAMETERS['info']['site_name']);
                $text = gdrcd_filter('out', $MESSAGE['register']['forms']['mail']['text'] . ': ' . $pass);

                // Tento l'invio della mail
                $hasEmailSent = mail($_POST['email'], $subject, $text, 'From: ' . $PARAMETERS['info']['webmaster_email']);
                // Aggiorno il feedback
                if($hasEmailSent) {
                    $feedback = gdrcd_filter('out', $MESSAGE['warning']['modified']);
                }

            }
            // Altrimenti segnalo l'errore
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
<strong><?php echo gdrcd_filter('out', $MESSAGE['homepage']['forms']['forgot']); ?></strong>
    <div class="pass_rec">
        <form action="index.php" method="post">
            <div>
                <span class="form_label"><label for="passrecovery"><?php echo $MESSAGE['homepage']['forms']['email']; ?></label></span>
                <input type="text" id="passrecovery" name="email"/>
            </div>
            <?=$feedback?>
            <input type="submit" value="<?php echo $MESSAGE['homepage']['forms']['new_pass']; ?>"/>
        </form>
    </div>