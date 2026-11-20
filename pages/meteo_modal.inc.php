<?php
/* HELP: Contenuto della modale aperta dall'icona del meteo nel box del luogo (popup.php?page=meteo_modal).
 * In una chat mostra il form rapido per il meteo della chat o della zona, nella mappa generale
 * il pannello di gestione di tutte le zone.
 */

$testi_meteo = $MESSAGE['interface']['meteo'];

/* Controllo permessi utente: stessa condizione dell'icona nel box del luogo */
if ((int)($_SESSION['permessi'] ?? 0) < METEO_PERM || ($PARAMETERS['mode']['auto_meteo'] ?? 'ON') != 'OFF') {
    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    return;
}

// Nella mappa generale (luogo = -1) il pannello completo, in una chat il form rapido
if ((int)($_SESSION['luogo'] ?? -1) <= 0) {
    include __DIR__ . '/gestione/meteo/index.inc.php';
    return;
}

$meteo_chat = gdrcd_meteo_corrente((int)$_SESSION['luogo'], (int)$_SESSION['mappa']);
$testi_pannello = $MESSAGE['interface']['administration']['meteo'];
?>
<div class="meteo_pannello meteo_modale">
    <h2 class="meteo_modale_titolo"><?php echo gdrcd_filter('out', $testi_pannello['title_chat']); ?></h2>

    <!-- Valore in vigore: stesso testo del box del luogo -->
    <p class="meteo_modale_corrente">
        <?php echo gdrcd_filter('out', $testi_pannello['current_col']); ?>:
        <strong id="meteoModalCorrente"><?php echo $meteo_chat['testo']; ?></strong>
    </p>

    <div id="meteoModalEsito" role="status"></div>

    <!-- Form meteo per i master: imposta o ripristina il meteo della chat o della zona -->
    <form action="ajax.php?page=chat&op=meteo" method="post" id="meteoForm" class="meteo_pannello_form">

        <!-- Temperatura, condizione, durata e bersaglio -->
        <?php echo gdrcd_meteo_form_campi($meteo_chat); ?>

        <!-- Tasti imposta/ripristina: il valore del tasto premuto viene inviato come "azione" -->
        <div class="input_container meteo_pulsanti">
            <button type="submit" name="azione" value="imposta">
                <?php echo gdrcd_filter('out', $testi_meteo['set']); ?>
            </button>
            <button type="submit" name="azione" value="ripristina" formnovalidate>
                <?php echo gdrcd_filter('out', $testi_meteo['reset']); ?>
            </button>
        </div>
    </form>

    <!-- Il pannello della zona ha bisogno di più spazio: riapro la stessa modale allargata -->
    <div class="link_back">
        <a href="popup.php?page=gestione/meteo<?php echo $meteo_chat['id_click'] !== null ? '#click-' . (int)$meteo_chat['id_click'] : ''; ?>"
           id="meteoLinkZona"
           data-title="<?php echo gdrcd_filter('out', $testi_pannello['title_zone']); ?>">
            <?php echo gdrcd_filter('out', $testi_pannello['link_zone']); ?>
        </a>
    </div>
</div>

<script>
    // jQuery viene caricato dal footer, dopo questo blocco
    window.addEventListener('load', function () {
        const esito = {
            imposta: <?php echo json_encode($testi_pannello['saved'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            ripristina: <?php echo json_encode($testi_pannello['reset_done'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        };
        const $esito = $('#meteoModalEsito');

        $('#meteoLinkZona').click(function (event) {
            // In un popup del browser non c'è una modale da riaprire: allargo la finestra e proseguo nella stessa
            if (window.opener) {
                window.resizeTo(Math.min(1200, screen.availWidth), Math.min(700, screen.availHeight));
                return;
            }

            const main = window.parent;

            if (main && typeof main.modalWindow === 'function') {
                event.preventDefault();
                main.modalWindow('meteo', $(this).data('title'), $(this).attr('href'),
                    Math.min(1200, main.innerWidth - 20), Math.min(700, main.innerHeight - 20));
            }
        });

        // serialize() non include il valore dei tasti di invio
        $('#meteoForm button[name="azione"]').click(function () {
            $('#meteoForm').data('azione', $(this).val());
        });

        $('#meteoForm').submit(function (event) {
            event.preventDefault();

            const azione = $(this).data('azione') || 'imposta';
            const data = $(this).serializeArray();
            data.push({name: 'azione', value: azione});

            $.post($(this).prop('action'), data)
                .done(function (response) {
                    $esito.attr('class', 'success').text(esito[azione]);

                    // Aggiorna il box del luogo e la chat nella pagina sotto la modale
                    const main = window.opener && !window.opener.closed ? window.opener : window.parent;
                    if (main && main !== window && main.$ && response.message) {
                        main.$('#meteo_testo').html(response.message.testo);
                        $('#meteoModalCorrente').html(response.message.testo);
                        if (typeof main.meteoUpdateExpiry === 'function') {
                            main.meteoUpdateExpiry(response.message);
                        }
                    }
                })
                .fail(function (jqXHR) {
                    let messaggio = jqXHR.responseText;
                    try {
                        messaggio = JSON.parse(messaggio).message;
                    } catch (e) {}
                    $esito.attr('class', 'error').text(messaggio);
                });
        });
    });
</script>
