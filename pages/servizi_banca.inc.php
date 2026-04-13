<?php /*HELP: */
$row = gdrcd_query("SELECT soldi, banca, ultimo_stipendio FROM personaggio WHERE id_personaggio = '".$_SESSION['id_personaggio']."' LIMIT 1");
$soldi = 0 + $row['soldi'];
$banca = 0 + $row['banca'];
$ultimo = $row['ultimo_stipendio'];

$query = "SELECT ruolo.stipendio FROM clgpersonaggioruolo LEFT JOIN ruolo on clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo WHERE clgpersonaggioruolo.id_personaggio = '".$_SESSION['id_personaggio']."'";
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
                    gdrcd_query("UPDATE personaggio SET soldi = soldi + ". gdrcd_filter_num($_POST['ammontare']) .", banca = banca - ". gdrcd_filter_num($_POST['ammontare']) ." WHERE id_personaggio = '".$_SESSION['id_personaggio']."' LIMIT 1");
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
                    gdrcd_query("UPDATE personaggio SET soldi = soldi - ".gdrcd_filter('num', $_POST['ammontare']).", banca = banca + ".gdrcd_filter_num($_POST['ammontare'])." WHERE id_personaggio = '".$_SESSION['id_personaggio']."' LIMIT 1");
                }
            } ?>
            <div class="link_back">
                <a href="main.php?page=servizi_banca"><?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['back']); ?></a>
            </div>
        <?php
        }
        /*Bonifico*/
        if((isset($_POST['op']) === true) && ($_POST['op'] == 'bonifico')) {
            $query = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '".gdrcd_filter_num($_POST['beneficiario'])."' LIMIT 1");


            if(empty($query)) {
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
                        gdrcd_query("UPDATE personaggio SET banca = banca - ".gdrcd_filter('num', $_POST['ammontare'])." WHERE id_personaggio = '".$_SESSION['id_personaggio']."' LIMIT 1");
                        gdrcd_query("UPDATE personaggio SET banca = banca + ".gdrcd_filter('num', $_POST['ammontare'])." WHERE id_personaggio = '".gdrcd_filter_num($_POST['beneficiario'])."' LIMIT 1");

                        /*Registro l'evento (Passaggio di danaro)*/
                        $personaggio = $query;
                        
                        gdrcd_log_info(
                            'Bonifico inviato a un altro personaggio',
                            [
                                'evento' => 'banca.invio_bonifico',
                                'direzione' => 'uscita',
                                'id_autore' => $_SESSION['id_personaggio'],
                                'autore' => $_SESSION['login'],
                                'id_soggetto' => $_SESSION['id_personaggio'],
                                'soggetto' => $_SESSION['login'],
                                'id_destinatario' => gdrcd_filter('num', $_POST['beneficiario']),
                                'destinatario' => $personaggio['nome'],
                                'ammontare' =>gdrcd_filter('num', $_POST['ammontare']),
                                'valuta' => $PARAMETERS['names']['currency']['plur'],
                                'causale' => $_POST['causale']
                            ],
                             $_SESSION['id_personaggio']
                        );
                        gdrcd_log_info(
                            'Bonifico ricevuto dal personaggio',
                            [
                                'evento' => 'banca.ricezione_bonifico',
                                'direzione' => 'entrata',
                                'id_autore' => $_SESSION['id_personaggio'],
                                'autore' => $_SESSION['login'],
                                'id_soggetto' => $_SESSION['id_personaggio'],
                                'soggetto' => $_SESSION['login'],
                                'id_destinatario' => gdrcd_filter('num', $_POST['beneficiario']),
                                'destinatario' => $personaggio['nome'],
                                'ammontare' =>gdrcd_filter('num', $_POST['ammontare']),
                                'valuta' => $PARAMETERS['names']['currency']['plur'],
                                'causale' => $_POST['causale']
                            ],
                             $_POST['beneficiario']
                        );
                        gdrcd_query("INSERT INTO messaggi (id_personaggio_mittente, id_personaggio_destinatario, spedito, testo) VALUES ('".$_SESSION['id_personaggio']."','".gdrcd_filter('in', $_POST['beneficiario'])."', NOW(), '".gdrcd_filter('in', $_SESSION['login'].' '.$MESSAGE['interface']['bank']['notice'].' '.gdrcd_filter('num', $_POST['ammontare']).' '.$PARAMETERS['names']['currency']['plur']).'. \n\n'.gdrcd_filter('in', $_POST['causale'])."')");
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
            gdrcd_query("UPDATE personaggio SET banca = banca + ".$stipendio.", ultimo_stipendio = NOW() WHERE id_personaggio = '".$_SESSION['id_personaggio']."' AND ultimo_stipendio < NOW() LIMIT 1");
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
                                $query = "SELECT id_personaggio, nome, cognome FROM personaggio WHERE permessi > -1 ORDER BY nome";
                                $nomi = gdrcd_query($query, 'result'); ?>
                                <option value="" selected>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['bank']['payee']); ?>
                                </option>
                                <?php while($option = gdrcd_query($nomi, 'fetch')) { ?>
                                    <option value="<?php echo $option['id_personaggio']; ?>">
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
