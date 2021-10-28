<?php
/*Controllo permessi utente*/
if ($_SESSION['permessi']<Functions::get_constant('TRAME_VIEW') && Functions::get_constant('TRAME_ENABLED')===FALSE){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} else { ?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>Lista delle trame</h2>
    </div>

    <div class="link_back">
        <a href="main.php?page=gestione_quest&op=new_trama">
            Registra nuova trama
        </a>
    </div>
<?php
    //Determinazione pagina (paginazione)
    $pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['records_per_page'];
    $pageend=$PARAMETERS['settings']['records_per_page'];

    if (Functions::get_constant('TRAME_VIEW_OTHER') || $_SESSION['permessi']>=Functions::get_constant('QUEST_SUPER_PERMISSION')) {
    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM trama");
    //Lettura record
    $result = gdrcd_query("SELECT * FROM trama ORDER BY data DESC LIMIT " . $pagebegin . ", " . $pageend . "", 'result');
    } else {
    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM trama WHERE autore = '".gdrcd_filter('in',$_SESSION['login'])."' ");
    //Lettura record
    $result = gdrcd_query("SELECT * FROM trama WHERE autore = '".gdrcd_filter('in',$_SESSION['login'])."' 
    ORDER BY data DESC LIMIT " . $pagebegin . ", " . $pageend . "", 'result');
    }
    $totaleresults = $record_globale['COUNT(*)'];
    $numresults=gdrcd_query($result, 'num_rows');

    /* Se esistono record */
    if ($numresults>0){

        while ($row=gdrcd_query($result, 'fetch')){
            if ($row['stato']==0) { $stato = '<b>In corso</b>';}
            if ($row['stato']==1) { $stato = 'Chiusa';}

            #Recupero le quest associate, se ce ne sono
            $quest = gdrcd_query("SELECT * FROM quest WHERE trama = ".gdrcd_filter('num',$row['id'])." 
            ORDER BY data DESC LIMIT " . $pagebegin . ", " . $pageend . "", 'result');
            $nums=gdrcd_query($quest, 'num_rows'); ?>

            <div class="elementi_elenco"> Titolo trama: <?php echo gdrcd_filter('out',$row['titolo']).' | Stato: '.$stato;?>
            <!-- Modifica -->
                <div class="controlli_elenco" style="float: right;">
                    <div class="controllo_elenco" >
                        <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest" method="post">
                            <input type="hidden" name="id_record" value="<?php echo $row['id']?>" />
                            <input type="hidden" name="op" value="edit_trama" />
                            <input type="image"
                                   src="imgs/icons/edit.png"
                                   alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                   title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                        </form>
                    </div>
                            <!-- Elimina -->
                            <div class="controllo_elenco" >
                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_quest" method="post">
                                    <input type="hidden" name="id_record" value="<?php echo $row['id']?>" />
                                    <input type="hidden" name="op" value="delete_trama" />
                                    <input type="image"
                                           src="imgs/icons/erase.png"
                                           onclick="return confirm('Vuoi davvero cancellare questa trama? Non potrà più essere recuperata')"
                                           alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                           title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"/>
                                </form>
                            </div>
                </div>
            </div>
<?php       echo '<div class="trama_container">
                    <div class="elementi_elenco">
                        Data inizio: '.gdrcd_format_date($row['data']).' | Autore: '.gdrcd_filter('out',$row['autore']).'
                         | Ultima modifica: '.gdrcd_filter('out',$row['ultima_modifica']).'
                    </div>';
            echo    '<div class="elementi_elenco">';
                echo 'Descrizione trama: '.gdrcd_filter('out',$row['descrizione']);
            echo    '</div>';
            echo    '<div class="trama_line">';
                        echo 'Lista quest associate:<br><br>';
                        if ($nums==0) {
                            echo '<div class="elementi_elenco">Nessuna quest associata</div>';
                        } else {
                            while ($list=gdrcd_query($quest, 'fetch')) {
                                echo '<div class="elementi_elenco"> Titolo: '.gdrcd_filter('out',$list['titolo']);
                                    echo ' | Data: '.gdrcd_format_date($row['data']).'</div>';
                                echo '<div class="elementi_elenco"> Autore: '.gdrcd_filter('out',$list['autore']);
                                    echo ' | Partecipanti: '.gdrcd_filter('out',$list['partecipanti']).'</div>';
                                echo '<div class="elementi_elenco">'.gdrcd_filter('out',$list['descrizione']).'</div>';
                            }
                        }
            echo    '</div>';
            echo    '</div>';
        }
    } ?>
    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest">
            Torna a gestione quest
        </a>
    </div>
<?php   } ?>