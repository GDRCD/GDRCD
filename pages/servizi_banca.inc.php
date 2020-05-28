<?php /*HELP: */
$row = gdrcd_query("SELECT soldi, banca, ultimo_stipendio FROM personaggio WHERE nome = '".$_SESSION['login']."' LIMIT 1");
$soldi = 0 + $row['soldi'];
$banca = 0 + $row['banca'];
$ultimo = $row['ultimo_stipendio'];

$query = "SELECT ruolo.stipendio FROM clgpersonaggioruolo LEFT JOIN ruolo on clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.personaggio = '".$_SESSION['login']."'";
$result = gdrcd_query($query, 'result');
$stipendio = 0;
while($row = gdrcd_query($result, 'fetch')) {
    $stipendio += $row['stipendio'];
}
gdrcd_query($result, 'free');
?>
<div class="pagina_servizi_banca">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['page_name']); ?></h2>
    </div>
    <!-- Operazioni bancarie -->
    <div class="page_body">
        <?php /*Prelievo*/
        if((isset($_POST['op']) === true) && (gdrcd_filter('get', $_POST['op']) == 'preleva')) {
            if(($_POST['ammontare'] <= 0) || (is_numeric($_POST['ammontare']) === false)) {
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['error']).'</div>';
            } else {
                if($_POST['ammontare'] > $banca) {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['withdraw_no']).'</div>';
                } else {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['done']).'</div>';
                    /*Eseguo la transazione*/
                    gdrcd_query("UPDATE personaggio SET soldi = soldi + ".$_POST['ammontare'].", banca = banca - ".$_POST['ammontare']." WHERE nome = '".$_SESSION['login']."' LIMIT 1");
                }
            } ?>
            <div class="link_back">
                <a href="main.php?page=servizi_banca"><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['back']); ?></a>
            </div>
        <?php
        }
        /*Deposito*/
        if((isset($_POST['op']) === true) && (gdrcd_filter('get', $_POST['op']) == 'deposita')) {
            if(($_POST['ammontare'] <= 0) || (is_numeric($_POST['ammontare']) === false)) {
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['error']).'</div>';
            } else {
                if($_POST['ammontare'] > $soldi) {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['deposit_no']).'</div>';
                } else {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['done']).'</div>';
                    /*Eseguo la transazione*/
                    gdrcd_query("UPDATE personaggio SET soldi = soldi - ".gdrcd_filter('num', $_POST['ammontare']).", banca = banca + ".$_POST['ammontare']." WHERE nome = '".$_SESSION['login']."' LIMIT 1");
                }
            } ?>
            <div class="link_back">
                <a href="main.php?page=servizi_banca"><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['back']); ?></a>
            </div>
        <?php
        }
        /*Bonifico*/
        if((isset($_POST['op']) === true) && ($_POST['op'] == 'bonifico')) {
            $query = gdrcd_query("SELECT nome FROM personaggio WHERE nome = '".$_POST['beneficiario']."' LIMIT 1");
            if(empty($_POST['beneficiario'])) {
                echo '<div class="warning">Il beneficiario che hai inserito non esiste o non &egrave; valido!</div>';
            } else {
                if(($_POST['ammontare'] <= 0) || (is_numeric($_POST['ammontare']) === false)) {
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['error']).'</div>';
                } else {
                    if($_POST['ammontare'] > $banca) {
                        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['withdraw_no']).'</div>';
                    } else {
                        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['done']).'</div>';
                        /*Eseguo la transazione*/
                        gdrcd_query("UPDATE personaggio SET banca = banca - ".gdrcd_filter('num', $_POST['ammontare'])." WHERE nome = '".$_SESSION['login']."' LIMIT 1");
                        gdrcd_query("UPDATE personaggio SET banca = banca + ".gdrcd_filter('num', $_POST['ammontare'])." WHERE nome = '".$_POST['beneficiario']."' LIMIT 1");

                        /*Registro l'evento (Passaggio di danaro)*/
                        gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) VALUES ('".gdrcd_filter('in', $_POST['beneficiario'])."', '".$_SESSION['login']."', NOW(), ".BONIFICO.", '".'('.gdrcd_filter('num', $_POST['ammontare']).' '.$PARAMETERS['names']['currency']['plur'].') '.gdrcd_filter('in', $_POST['causale'])."')");
                        gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."','".gdrcd_capital_letter(gdrcd_filter('in', $_POST['beneficiario']))."', NOW(), '".gdrcd_filter('in', $_SESSION['login'].' '.$MESSAGE['interface']['bank']['notice'].' '.gdrcd_filter('num', $_POST['ammontare']).' '.$PARAMETERS['names']['currency']['plur']).'. \n\n'.gdrcd_filter('in', $_POST['causale'])."')");
                    }
                }
            } ?>
            <div class="link_back">
                <a href="main.php?page=servizi_banca"><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['back']); ?></a>
            </div>
        <?php
        }
        /*Stipendio*/
        /**    * Correzione dell'exploit che rendeva possibile accreditarsi un numero illimitato di soldi in banca
         * Il controllo è eseguito anche nella query con la condizione 'AND ultimo_stipendio < NOW()'.
         * Un grazie a Dyrr per la segnalazione.
         * @author Blancks
         */
        if((isset($_POST['op']) === true) && ($_POST['op'] == 'incassa') && ($ultimo != strftime("%Y-%m-%d"))) {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['bank']['done']).'</div>';
            gdrcd_query("UPDATE personaggio SET banca = banca + ".$stipendio.", ultimo_stipendio = NOW() WHERE nome = '".$_SESSION['login']."' AND ultimo_stipendio < NOW() LIMIT 1");
            ?>
            <div class="link_back">
                <a href="main.php?page=servizi_banca"><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['back']); ?></a>
            </div>
        <?php
        }
        /*Visualizzazione di base*/
        if(isset($_POST['op']) === false) { ?>
            <div class="panels_box">
                <div class="status_bancario">
                    <!-- Saldo bancario -->
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['amount'].": ".$banca." ".$PARAMETERS['names']['currency']['plur']); ?>
                    <br />
                    <!-- Stipendio -->
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['per_day'].": ".$stipendio." ".$PARAMETERS['names']['currency']['plur']); ?>
                    <br />
                    <!-- In tasca -->
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['pocket'].": ".$soldi." ".$PARAMETERS['names']['currency']['plur']); ?>
                    <br />
                </div>
                <!-- Deposito -->
                <div class="form_gioco">
                    <form action="main.php?page=servizi_banca"
                          method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['deposit']) ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="ammontare" class="form_gestione_input" value="0" />
                        </div>
                        <div class='form_submit'>
                            <input name="op" type="hidden" class="form_gestione_input" value="deposita" />
                            <input name="conferma" type="submit" class="form_gestione_input" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['execute']) ?>" />
                        </div>
                    </form>
                </div>
                <!-- Prelievo -->
                <div class="form_gioco">
                    <form action="main.php?page=servizi_banca" method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['withdraw']) ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="ammontare" class="form_gestione_input" value="0" />
                        </div>
                        <div class='form_submit'>
                            <input name="op" type="hidden" class="form_gestione_input" value="preleva" />
                            <input name="conferma" type="submit" class="form_gestione_input" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['execute']) ?>" />
                        </div>
                    </form>
                </div>
                <!-- Bonifico -->
                <div class="form_gioco">
                    <form action="main.php?page=servizi_banca" method="post">
                        <div class="form_label">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['payment']) ?>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="ammontare" class="form_gestione_input" value="0" />
                        </div>
                        <div class='form_field'>
                            <input name="op" type="hidden" class="form_gestione_input" value="bonifico" />
                            <select name="beneficiario" class="form_gestione_selectbox">
                                <!-- PG -->
                                <?php
                                $query = "SELECT nome, cognome FROM personaggio WHERE permessi > -1 ORDER BY nome";
                                $nomi = gdrcd_query($query, 'result'); ?>
                                <option value="" selected>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['payee']); ?>
                                </option>
                                <?php while($option = gdrcd_query($nomi, 'fetch')) { ?>
                                    <option value="<?php echo $option['nome']; ?>">
                                        <?php echo gdrcd_filter('out', $option['nome'])." ".gdrcd_filter('out', $option['cognome']); ?>
                                    </option>
                                <?php }//while
                                gdrcd_query($nomi, 'free');
                                ?>
                            </select>
                        </div>
                        <div class='form_field'>
                            <input type="text" name="causale" class="form_gestione_input" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['cause']); ?>" />
                        </div>
                        <div class='form_submit'>
                            <input name="conferma" type="submit" class="form_gestione_input" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['execute']); ?>" />
                        </div>
                    </form>
                </div>
                <!-- Stipendio -->
                <?php
                if($ultimo >= strftime("%Y-%m-%d")) {
                    echo gdrcd_filter('out', $MESSAGE['interface']['bank']['credit_no']);
                } else {
                    if($stipendio > 0) { ?>
                        <div class="form_gioco">
                            <form
                                    action="main.php?page=servizi_banca"
                                    method="post">
                                <div class="form_label">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['pay']).' ('.gdrcd_filter('out', $MESSAGE['interface']['bank']['credit']
                                        ).': '.$stipendio.' '.$PARAMETERS['names']['currency']['plur'].') '; ?>
                                </div>
                                <div class='form_submit'>
                                    <input name="ammontare" type="hidden" class="form_gestione_input" value="<?php echo $stipendio; ?>" />
                                    <input name="op" type="hidden" class="form_gestione_input" value="incassa" />
                                    <input name="conferma" type="submit" class="form_gestione_input" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['execute']) ?>" />
                                </div>
                            </form>
                        </div>
                    <?php } else {
                        echo gdrcd_filter('out', $MESSAGE['interface']['bank']['credit']).": ".$stipendio." ".$PARAMETERS['names']['currency']['plur']." ";
                    }
                } ?>
            </div>
        <?php } ?>
    </div>
    <!-- banca_operazioni-->
</div><!-- banca_box -->
