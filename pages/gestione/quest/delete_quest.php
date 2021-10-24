<?php /* Cancellatura in un record */
if ($_POST['op']=='delete_quest'){
    #Verifico che l'id passato sia valido
    if ($_SESSION['permessi']<EDIT_ALL_QUEST){
        $id=gdrcd_query("SELECT * FROM quest WHERE id = '".$_POST['id_record']."' AND autore ='".$_SESSION['login']."'", 'result');
    } else {
        $id=gdrcd_query("SELECT * FROM quest WHERE id = '".$_POST['id_record']."' ", 'result');
    }
    $quest_num = gdrcd_query($id, 'num_rows');

    if ($quest_num>0){

        /*Carico il record da cancellare*/
        $loaded_record=gdrcd_query("SELECT * FROM quest WHERE id=".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1 ");
        $parts = explode(', ', $loaded_record['partecipanti']); //array partecipanti
        #Caricamento lista presenti
        $i=0;
        for ($i=0; $i<10; $i++) {
            $a = $i+1;

            if ($parts[$i]!=='') {

                #Recupero i pg coinvolti
                $pg="SELECT * FROM clgpgquest WHERE id_quest = ".gdrcd_filter('num',$_POST['id_record'])." 
                    AND nome_pg= '".gdrcd_filter('in',$parts[$i])."' ";
                $res_pg=gdrcd_query($pg, 'result');
                $rec_pg=gdrcd_query($res_pg, 'fetch');

                $numresults=gdrcd_query($res_pg, 'num_rows');


                if ($numresults>0) {
                    #Elimino il record in clgpgquest
                    gdrcd_query("DELETE FROM clgpgquest
                        WHERE id_quest = ".gdrcd_filter('num',$_POST['id_record'])." 
                        AND nome_pg = '".gdrcd_filter('in',$parts[$i])."' ");

                    #Elimino i px assegnati
                    gdrcd_query("UPDATE personaggio SET esperienza = esperienza - ".gdrcd_filter('num',$rec_pg['px_assegnati'])." 
                    WHERE nome = '".gdrcd_filter('in',$parts[$i])."' LIMIT 1 ");

                    /*Registro l'operazione*/
                    $cause = 'Cancellazione Quest: '.gdrcd_filter('in',$loaded_record['titolo']);
                    gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) 
                            VALUES 
                            ('".gdrcd_filter('in',$parts[$i])."', '".$_SESSION['login']."', NOW(), 
                            ".PX.", '(-".gdrcd_filter('num',$rec_pg['px_assegnati']).' px) '.gdrcd_filter('in',$cause)."')");

                    #Notifica
                    if (QUEST_ALERT) {
                        $text = 'Il resoconto quest relativo alla Quest: <b>' . $loaded_record['titolo'] . '</b> è stato cancellato da 
                                ' . gdrcd_filter('in', $_SESSION['login']);
                        gdrcd_query("INSERT INTO messaggi (destinatario, mittente, spedito, oggetto, testo) VALUES 
                                    ('" . gdrcd_filter('in', $parts[$i]) . "',
                                    'Resoconti Quest',  NOW(), 'Cancellazione resoconto quest', 
                                    '" . $text . "')");

                    }
                }
            }
        }

        /*Eseguo la cancellazione della quest intera */
        gdrcd_query("DELETE FROM quest WHERE id=".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1");
        ?>
        <div class="warning">
            <?php echo gdrcd_filter('out',$MESSAGE['warning']['deleted']);?>
        </div>
<?php   } else { ?>
        <div class="warning">
            Non hai i permessi per cancellare questa Quest
        </div>
<?php   } ?>
<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_quest">
        Torna a gestione quest
    </a>
</div>
<?php } ?>