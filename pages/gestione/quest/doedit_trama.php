<?php /*Modifica di un record*/
if ($_POST['op']=='doedit_trama'){
        /*Processo le informazioni ricevute dal form*/

    if ($_SESSION['permessi']>=Functions::get_constant('TRAME_PERM') && Functions::get_constant('TRAME_ENABLED')) {
        #Verifico che l'id passato sia valido
        if ($_SESSION['permessi'] < Functions::get_constant('QUEST_SUPER_PERMISSION')) {
            $id = gdrcd_query("SELECT * FROM trama WHERE id = '" . $_POST['id_record'] . "' 
            AND autore ='" . $_SESSION['login'] . "'", 'result');
        } else {
            $id = gdrcd_query("SELECT * FROM trama WHERE id = '" . $_POST['id_record'] . "' ", 'result');
        }
        $trama_num = gdrcd_query($id, 'num_rows');
    } else { $trama_num = 0; }
        if ($trama_num>0) {
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE trama SET titolo ='".gdrcd_filter('in',$_POST['titolo'])."', 
                    ultima_modifica ='".gdrcd_filter('in',$_SESSION['login'])."',
                    descrizione ='".gdrcd_filter('in',$_POST['descrizione'])."', stato = ".gdrcd_filter('num',$_POST['stato'])." 
                    WHERE id = ".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
                </div>
        <?php } else { ?>
            <div class="warning">
                Impossibile modificare questa trama
            </div>
        <?php   } ?>
        <!-- Link di ritorno alla visualizzazione di base -->
        <div class="link_back">
            <a href="main.php?page=gestione_quest&op=lista_trame">
                Torna alla gestione delle trame
            </a>
        </div>
<?php   } ?>