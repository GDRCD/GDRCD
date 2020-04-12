<div class="pagina_scheda">


<<<<<<< HEAD
    <?php /*HELP: E' possibile modificare la scheda agendo su scheda.css nel tema scelto, oppure sostituendo il codice che segue la voce "Scheda del personaggio"*/ ?>

    <?php
    /********* CARICAMENTO PERSONAGGIO ***********/
    //Se non e' stato specificato il nome del pg
    if (isset($_REQUEST['pg']) === false)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
    } else
    {
        $query = "SELECT personaggio.*, razza.sing_m, razza.sing_f, razza.id_razza, razza.bonus_car0, razza.bonus_car1, razza.bonus_car2, razza.bonus_car3, razza.bonus_car4, razza.bonus_car5 FROM personaggio LEFT JOIN razza ON personaggio.id_razza=razza.id_razza WHERE personaggio.nome = '" . gdrcd_filter('in',
                $_REQUEST['pg']) . "'";
        $result = gdrcd_query($query, 'result');
        //Se non esiste il pg
        if (gdrcd_query($result, 'num_rows') == 0)
        {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
        } else
        {
            $record = gdrcd_query($result, 'fetch');
            gdrcd_query($result, 'free');


            $bonus_oggetti = gdrcd_query("SELECT SUM(oggetto.bonus_car0) AS BO0, SUM(oggetto.bonus_car1) AS BO1, SUM(oggetto.bonus_car2) AS BO2, SUM(oggetto.bonus_car3) AS BO3, SUM(oggetto.bonus_car4) AS BO4, SUM(oggetto.bonus_car5) AS BO5 FROM oggetto JOIN clgpersonaggiooggetto ON oggetto.id_oggetto = clgpersonaggiooggetto.id_oggetto WHERE clgpersonaggiooggetto.nome = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "' AND clgpersonaggiooggetto.posizione > " . ZAINO . "");

            /*Controllo esilio, se esiliato non visualizzo la scheda*/
            if ($record['esilio'] > strftime('%Y-%m-%d'))
            {
                echo '<div class="warning">' . gdrcd_filter('out', $record['nome']) . ' ' . gdrcd_filter('out',
                        $record['cognome']) . ' ' . gdrcd_filter('out',
                        $MESSAGE['warning']['character_exiled']) . ' ' . gdrcd_format_date($record['esilio']) . ' (' . $record['motivo_esilio'] . ' - ' . $record['autore_esilio'] . ')</div>';
                if ($_SESSION['permessi'] >= GAMEMASTER)
                {
                    ?>
                    <div class="panels_box">
                        <div class="form_gioco">
                            <form action="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url',
                                $_REQUEST['pg']) ?>" method="post">
                                <input type="hidden" value="<?php echo strftime('%Y'); ?>" name="year"/>
                                <input type="hidden" value="<?php echo strftime('%m'); ?>" name="month"/>
                                <input type="hidden" value="<?php echo strftime('%d'); ?>" name="day"/>
                                <input type="hidden" value="<?php gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>" name="causale"/>
                                <input type="hidden" value="exile" name="op"/>

                                <div class="form_label">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>
                                </div>
                                <div class="form_submit">
                                    <input type="submit"
                                           value="<?php echo gdrcd_filter('out',
                                               $MESSAGE['interface']['forms']['submit']); ?>"/>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php }
            } else
            {
                $px_totali_pg = $record['esperienza'];

                //carico le sole abilità del pg
                $result = gdrcd_query("SELECT id_abilita, grado FROM clgpersonaggioabilita WHERE nome='" . gdrcd_filter('in',
                        $_REQUEST['pg']) . "'", 'result');
                $px_spesi = 0;
                while ($row = gdrcd_query($result, 'fetch'))
                {
                    /*Costo in px della singola abilità*/
                    $px_abi = $PARAMETERS['settings']['px_x_rank'] * (($row['grado'] * ($row['grado'] + 1)) / 2);
                    /*Costo totale*/
                    $px_spesi += $px_abi;
                    $ranks[$row['id_abilita']] = $row['grado'];
                }

                gdrcd_query($result, 'free');

                /*Incremento skill*/
                if ((gdrcd_filter('get', $_REQUEST['op']) == 'addskill') && (($_SESSION['login'] == gdrcd_filter('out',
                                $_REQUEST['pg'])) || ($_SESSION['permessi'] >= MODERATOR))
                )
                {
                    $px_necessari = $PARAMETERS['settings']['px_x_rank'] * ($ranks[$_REQUEST['what']] + 1);
                    if (($px_totali_pg - $px_spesi) >= $px_necessari)
                    {
                        $px_spesi += $px_necessari;
                        if ($px_necessari == $PARAMETERS['settings']['px_x_rank'])
                        {
                            $query = "INSERT INTO clgpersonaggioabilita (id_abilita, nome, grado) VALUES (" . gdrcd_filter('num',
                                    $_REQUEST['what']) . ", '" . gdrcd_filter('in', $_REQUEST['pg']) . "', 1)";
                            $ranks[$_REQUEST['what']] = 1;
                            #echo $query;
                        } else
                        {
                            $ranks[$_REQUEST['what']]++;
                            $query = "UPDATE clgpersonaggioabilita SET grado = " . $ranks[$_REQUEST['what']] . " WHERE id_abilita = " . gdrcd_filter('num',
                                    $_REQUEST['what']) . " AND nome = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'";
                        }//else
                        gdrcd_query($query);
                        echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';
                    }//if
                }//if

                /*Decremento skill*/
                if ((gdrcd_filter('get', $_REQUEST['op']) == 'subskill') && ($_SESSION['permessi'] >= MODERATOR))
                {
                    if ($ranks[$_REQUEST['what']] == 1)
                    {
                        $query = "DELETE FROM clgpersonaggioabilita WHERE id_abilita = " . $_REQUEST['what'] . " AND nome = '" . gdrcd_filter('in',
                                $_REQUEST['pg']) . "' LIMIT 1";
                        $ranks[$_REQUEST['what']] = 0;
                    } else
                    {
                        $ranks[$_REQUEST['what']]--;
                        $query = "UPDATE clgpersonaggioabilita SET grado = " . $ranks[$_REQUEST['what']] . " WHERE id_abilita = " . $_REQUEST['what'] . " AND nome = '" . gdrcd_filter('in',
                                $_REQUEST['pg']) . "'";
                    }//else
                    gdrcd_query($query);
                    echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['modified']) . '</div>';
                }//if


                if (isset($_REQUEST['op']) === false)
                {
                    ?>

                    <!--- SCHEDA DEL PERSONAGGIO --->


                    <div class="page_title">
                        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
                    </div>

                    <div class="page_body">

                        <?php
                        /** * Controllo e avviso che è ora di cambiare password
                         * @author Blancks
                         */
                        if ($PARAMETERS['mode']['alert_password_change'] == 'ON')
                        {
                            $six_months = 15552000;
                            $ts_signup = strtotime($record['data_iscrizione']);
                            $ts_lastpass = (int) strtotime($record['ultimo_cambiopass']);


                            if ($ts_lastpass + $six_months < time() && $record['nome'] == $_SESSION['login'])
                            {
                                echo '<div class="warning">';

                                if ($ts_signup + $six_months < time())
                                {
                                    echo $MESSAGE['warning']['changepass'];
                                } else
                                {
                                    echo $MESSAGE['warning']['changepass_signup'];
                                }


                                echo '</div>';
                            }

                        }

                        ?>


                        <div class="menu_scheda"><!-- Menu scheda -->


                            <?php /*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
                            if ($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= GUILDMODERATOR)
                            { ?>
                                <a href="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url',
                                    $_REQUEST['pg']); ?>">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['update']); ?>
                                </a>
                            <?php } ?>

                            <a href="main.php?page=scheda_trans&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['menu']['transictions']); ?>
                            </a>

                            <a href="main.php?page=scheda_px&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['experience']); ?>
                            </a>

                            <a href="main.php?page=scheda_oggetti&pg=<?php echo gdrcd_filter('url',
                                $_REQUEST['pg']); ?>">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['inventory']); ?>
                            </a>
                            <a href="main.php?page=scheda_equip&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['equipment']); ?>
                            </a>

                            <?php /*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
                            if ($_SESSION['permessi'] >= MODERATOR)
                            { ?>
                                <a href="main.php?page=scheda_log&pg=<?php echo gdrcd_filter('url',
                                    $_REQUEST['pg']); ?>">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['log']); ?>
                                </a>
                                <a href="main.php?page=scheda_gst&pg=<?php echo gdrcd_filter('url',
                                    $_REQUEST['pg']); ?>">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['gst']); ?>
                                </a>
                            <?php } ?>

                        </div>
                        <!-- Menu scheda -->
=======
<?php /*HELP: E' possibile modificare la scheda agendo su scheda.css nel tema scelto, oppure sostituendo il codice che segue la voce "Scheda del personaggio"*/ ?>

<?php
/********* CARICAMENTO PERSONAGGIO ***********/
//Se non e' stato specificato il nome del pg
if (isset($_REQUEST['pg'])===FALSE){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['unknown_character_sheet']).'</div>';
} else {
	$query = "SELECT personaggio.*, razza.sing_m, razza.sing_f, razza.id_razza, razza.bonus_car0, razza.bonus_car1, razza.bonus_car2, razza.bonus_car3, razza.bonus_car4, razza.bonus_car5 FROM personaggio LEFT JOIN razza ON personaggio.id_razza=razza.id_razza WHERE personaggio.nome = '".gdrcd_filter('in',$_REQUEST['pg'])."'";
	$result = gdrcd_query($query, 'result');
	//Se non esiste il pg
	if (gdrcd_query($result, 'num_rows')==0){echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['unknown_character_sheet']).'</div>';}

	else {
		$record = gdrcd_query($result, 'fetch');
		gdrcd_query($result, 'free');


		$bonus_oggetti = gdrcd_query("SELECT SUM(oggetto.bonus_car0) AS BO0, SUM(oggetto.bonus_car1) AS BO1, SUM(oggetto.bonus_car2) AS BO2, SUM(oggetto.bonus_car3) AS BO3, SUM(oggetto.bonus_car4) AS BO4, SUM(oggetto.bonus_car5) AS BO5 FROM oggetto JOIN clgpersonaggiooggetto ON oggetto.id_oggetto = clgpersonaggiooggetto.id_oggetto WHERE clgpersonaggiooggetto.nome = '".gdrcd_filter('in',$_REQUEST['pg'])."' AND clgpersonaggiooggetto.posizione > ".ZAINO."");

        /*Controllo esilio, se esiliato non visualizzo la scheda*/
		if($record['esilio']>strftime('%Y-%m-%d')){
           echo '<div class="warning">'.gdrcd_filter('out',$record['nome']).' '.gdrcd_filter('out',$record['cognome']).' '.gdrcd_filter('out',$MESSAGE['warning']['character_exiled']).' '.gdrcd_format_date($record['esilio']).' ('.$record['motivo_esilio'].' - '.$record['autore_esilio'].')</div>';
           if ($_SESSION['permessi']>=GAMEMASTER){?>
              <div class="panels_box"><div class="form_gioco">
              <form action="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url',$_REQUEST['pg']) ?>" method="post">
			      <input type="hidden" value="<?php echo strftime('%Y'); ?>" name="year" />
			      <input type="hidden" value="<?php echo strftime('%m'); ?>" name="month" />
			      <input type="hidden" value="<?php echo strftime('%d'); ?>" name="day" />
				  <input type="hidden" value="<?php gdrcd_filter('out',$MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>" name="causale" />
			      <input type="hidden" value="exile" name="op" />
				  <div class="form_label">
				      <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['modify_form']['unexile']); ?>
				  </div>
				  <div class="form_submit">
				      <input type="submit"
					         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']); ?>" />
				  </div>
			  </form>
			  </div></div>
           <?php }
		} else {
		 $px_totali_pg=$record['esperienza'];

         //carico le sole abilità del pg
         $result=gdrcd_query("SELECT id_abilita, grado FROM clgpersonaggioabilita WHERE nome='".gdrcd_filter('in',$_REQUEST['pg'])."'", 'result');
         $px_spesi=0;
         while ($row=gdrcd_query($result, 'fetch')){
	       /*Costo in px della singola abilità*/
           $px_abi=$PARAMETERS['settings']['px_x_rank']*(($row['grado']*($row['grado']+1))/2);
	       /*Costo totale*/
	       $px_spesi+=$px_abi;
	       $ranks[$row['id_abilita']]=$row['grado'];
         }

		 gdrcd_query($result, 'free');
		 
        /* Nuovo Salvataggio Skill */
        if (isset($_POST['op']) && !empty($_POST['skills']) && $_POST['op'] === 'saveskills' && ($_SESSION['login']==$_REQUEST['pg'] || $_SESSION['permessi'] >= MODERATOR)) {
            // Raccogliamo i dati dal form
            $skills = json_decode($_POST['skills'], true);
            
            if (!empty($skills)) {
                // ci prepariamo a conteggiare i px necessari per eseguire le modifiche
                $px_necessari = 0;
                
                // Recuperiamo tutti gli id delle skills da modificare
                $skillsIdList = array_keys($skills);
                
                // E assicuriamoci di filtrarli tutti a dovere
                foreach ($skillsIdList as &$skillId) {
                    $skillId = gdrcd_filter('num', $skillId);
                    
                    // Già che ci siamo comunque controlliamo quanti punti bisogna spendere per salvare tutto
                    if ((!isset($ranks[$skillId]) && $skills[$skillId] > 0) || $skills[$skillId] != $ranks[$skillId]) {
                    
                        // intanto cerchiamo di capire se stiamo effettuando un incremento o un decremento
                        $diff = $skills[$skillId] - (!isset($ranks[$skillId])? 0 : $ranks[$skillId]);
                        
                        // incremento
                        if ($diff > 0) {
                            
                            // conteggiamo quindi tutti i px da sottrarre per ogni step di incremento
                            while ($diff-- > 0) {
                                $px_necessari += $PARAMETERS['settings']['px_x_rank']*($skills[$skillId]-$diff);
                            }
                            
                        // decremento, ma solo se si è autorizzati
                        } elseif ($_SESSION['permessi'] >= MODERATOR) {
                            // per il decremento la logica è la stessa, semplicemente recuperiamo il valore assoluto di diff
                            $diff = abs($diff);
                            
                            // e recuperiamo i px per ogni step di decremento dal valore precedente
                            while ($diff-- > 0) {
                                $px_necessari -= $PARAMETERS['settings']['px_x_rank']*($ranks[$skillId]-$diff);
                            }
                        }
                    } else {
                        // se questa skill non ha ricevuto modifiche la rimuoviamo dall'elenco
                        unset($skills[$skillId]);
                    }
                }
                
                // Controlliamo se il personaggio ha il necessario numero di punti esperienza per convalidare le modifiche
                if ($px_totali_pg-$px_spesi-$px_necessari >= 0) {
                    /* 
                      A questo punto ci prepariamo per un  controllo:
                        - verifichiamo che gli id delle abilità proposteci dal form esistano davvero nella tabella abilità
                    */
                    $skillsResult = gdrcd_query(
                        "SELECT id_abilita
                        FROM abilita
                        WHERE id_abilita IN(". implode(',', $skillsIdList) .")",
                        'result'
                    );
                    
                    // Se la query ritorna un qualche risultato
                    if (gdrcd_query($skillsResult, 'num_rows') > 0) {
                        
                        // Li scorriamo tutti
                        while ($row = gdrcd_query($skillsResult, 'fetch')) {
                            $skillId = $row['id_abilita'];
                            
                            // Il personaggio non ha nulla nel database registrato per questa skill, andiamo a creare il record
                            if (!isset($ranks[$skillId]) && isset($skills[$skillId])) {
                            
                                gdrcd_query(
                                    "INSERT INTO clgpersonaggioabilita (id_abilita, nome, grado) 
                                    VALUES (
                                        ". $skillId .", 
                                        '". gdrcd_filter('in',$_REQUEST['pg']) ."', 
                                        ". gdrcd_filter('num', $skills[$skillId]) ."
                                    )"
                                );
                            
                            // Il personaggio ha già questa skill ad un qualche valore, quindi dobbiamo effettuare un aggiornamento
                            } elseif (
                                isset($ranks[$skillId]) && 
                                isset($skills[$skillId]) && 
                                (
                                    ($_SESSION['permessi'] >= MODERATOR) ||
                                    ($_SESSION['login'] == $_REQUEST['pg'] && $ranks[$skillId] < $skills[$skillId])
                                )
                            ) {
                            
                                gdrcd_query(
                                    "UPDATE clgpersonaggioabilita 
                                        SET grado = ". gdrcd_filter('num', $skills[$skillId]) ."
                                    WHERE id_abilita = ". $skillId ." 
                                        AND nome LIKE '". gdrcd_filter('in',$_REQUEST['pg']) ."'
                                    LIMIT 1"
                                );
                                
                            }
                            
                        }
                        
                        // puliamo le risorse impiegate dalla query
                        gdrcd_query($skillsResult, 'free');
                    }
                
                    echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['warning']['modified']).'</div>';
                    
                } else {
                    echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['warning']['cant_do']).'</div>';
                }
                
            } else {
                echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['warning']['cant_do']).'</div>';
            }
        }
         
		 /*Incremento skill*/
         if((gdrcd_filter('get',$_REQUEST['op'])=='addskill') && (($_SESSION['login']==gdrcd_filter('out',$_REQUEST['pg']))||($_SESSION['permessi']>=MODERATOR))){
            $px_necessari=$PARAMETERS['settings']['px_x_rank']*($ranks[$_REQUEST['what']]+1);
            if(($px_totali_pg-$px_spesi)>=$px_necessari){
              $px_spesi+=$px_necessari;
              if ($px_necessari==$PARAMETERS['settings']['px_x_rank']){
                 $query="INSERT INTO clgpersonaggioabilita (id_abilita, nome, grado) VALUES (".gdrcd_filter('num',$_REQUEST['what']).", '".gdrcd_filter('in',$_REQUEST['pg'])."', 1)";
                 $ranks[$_REQUEST['what']]=1;
                 #echo $query;
			  } else {
                 $ranks[$_REQUEST['what']]++;
				 $query="UPDATE clgpersonaggioabilita SET grado = ".$ranks[$_REQUEST['what']]." WHERE id_abilita = ".gdrcd_filter('num',$_REQUEST['what'])." AND nome = '".gdrcd_filter('in',$_REQUEST['pg'])."'";
              }//else
              gdrcd_query($query);
              echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['warning']['modified']).'</div>';
            }//if
         }//if

         /*Decremento skill*/
         if((gdrcd_filter('get',$_REQUEST['op'])=='subskill') && ($_SESSION['permessi']>=MODERATOR)){
            if ($ranks[$_REQUEST['what']]==1){
               $query="DELETE FROM clgpersonaggioabilita WHERE id_abilita = ".$_REQUEST['what']." AND nome = '".gdrcd_filter('in',$_REQUEST['pg'])."' LIMIT 1";
               $ranks[$_REQUEST['what']]=0;
            } else {
			   $ranks[$_REQUEST['what']]--;
               $query="UPDATE clgpersonaggioabilita SET grado = ".$ranks[$_REQUEST['what']]." WHERE id_abilita = ".$_REQUEST['what']." AND nome = '".gdrcd_filter('in',$_REQUEST['pg'])."'";
            }//else
            gdrcd_query($query);
            echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['warning']['modified']).'</div>';
        }//if


if (isset($_REQUEST['op'])===FALSE){
?>

<!--- SCHEDA DEL PERSONAGGIO --->


<div class="page_title">
   <h2><?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['page_name']); ?></h2>
</div>

<div class="page_body">

<?php
/** * Controllo e avviso che è ora di cambiare password
	* @author Blancks
*/
if ($PARAMETERS['mode']['alert_password_change']=='ON')
{
	$six_months 	= 15552000;
	$ts_signup		= strtotime($record['data_iscrizione']);
	$ts_lastpass 	= (int)strtotime($record['ultimo_cambiopass']);


	if ($ts_lastpass+$six_months < time() && $record['nome'] == $_SESSION['login'])
	{
		echo '<div class="warning">';

		if ($ts_signup+$six_months < time())
				echo $MESSAGE['warning']['changepass'];
		else
				echo $MESSAGE['warning']['changepass_signup'];


		echo '</div>';
	}
>>>>>>> master


                        <div class="ritratto"><!-- nome, ritratto, ultimo ingresso -->

                            <div class="titolo_box">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['box_title']['portrait']); ?>
                            </div>

                            <div class="ritratto_nome">
     <span class="ritratto_nome_nome">
	    <?php echo gdrcd_filter('out', $record['nome']); ?>
	 </span>
	 <span class="ritratto_nome_cognome">
	    <?php echo gdrcd_filter('out', $record['cognome']); ?>
	 </span>
<<<<<<< HEAD
                            </div>

                            <div class="ritratto_avatar">
                                <img src="<?php echo gdrcd_filter('fullurl', $record['url_img']); ?>"
                                     class="ritratto_avatar_immagine"/>
                            </div>

                            <div class="iscritto_da">
                                <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['first_login']) . ' ' . gdrcd_format_date($record['data_iscrizione']); ?>
                            </div>
                            <?php if (gdrcd_format_date($record['ora_entrata']) != '00/00/0000')
                            { ?>
                                <div class="ultimo_ingresso">
                                    <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['last_login']) . ' ' . gdrcd_format_date($record['ora_entrata']); ?>
                                </div>
                            <?php } ?>


                            <div class="ritratto_invia_messaggio"><!-- Link invia messaggio -->
                                <a href="main.php?page=messages_center&newmessage=yes&reply_dest=<?php echo gdrcd_filter('url',
                                    $record['nome']); ?>" class="link_invia_messaggio">
                                    <?php if (empty($PARAMETERS['names']['private_message']['image_file']) === false)
                                    { ?>
                                        <img src="<?php echo $PARAMETERS['names']['private_message']['image_file']; ?>"
                                             alt="<?php echo gdrcd_filter('out',
                                                     $MESSAGE['interface']['sheet']['send_message_to']['send']) . ' ' . gdrcd_filter('out',
                                                     $PARAMETERS['names']['private_message']['sing']) . ' ' . gdrcd_filter('out',
                                                     $MESSAGE['interface']['sheet']['send_message_to']['to']) . ' ' . gdrcd_filter('out',
                                                     $record['nome']); ?>"
                                             title="<?php echo gdrcd_filter('out',
                                                     $MESSAGE['interface']['sheet']['send_message_to']['send']) . ' ' . gdrcd_filter('out',
                                                     $PARAMETERS['names']['private_message']['sing']) . ' ' . gdrcd_filter('out',
                                                     $MESSAGE['interface']['sheet']['send_message_to']['to']) . ' ' . gdrcd_filter('out',
                                                     $record['nome']); ?>"
                                             class="link_messaggio_forum">
                                    <?php } else
                                    {
                                        echo gdrcd_filter('out',
                                                $MESSAGE['interface']['sheet']['send_message_to']['send']) . ' ' . gdrcd_filter('out',
                                                strtolower($PARAMETERS['names']['private_message']['sing'])) . ' ' . gdrcd_filter('out',
                                                $MESSAGE['interface']['sheet']['send_message_to']['to']) . ' ' . gdrcd_filter('out',
                                                $record['nome']);
                                    } ?>
                                </a>
                            </div>
                            <!-- Link invia messaggio -->

                        </div>
                        <!-- nome, ritratto, ultimo ingresso, abiti portati -->


                        <div class="profilo"><!-- Punteggi, salute, status, classe, razza. -->

                            <div class="titolo_box">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['box_title']['profile']); ?>
                            </div>

                            <?php if ($record['permessi'] > 0)
                            { ?>
                                <div class="profilo_voce">
                                    <div class="profilo_voce_label">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['profile']['role']); ?>:
                                    </div>
                                    <div class="profilo_voce_valore">
                                        <?php
                                        switch ($record['permessi'])
                                        {
                                            case USER:
                                                $permessi_utente = '';
                                                break;
                                            case GUILDMODERATOR:
                                                $permessi_utente = $PARAMETERS['names']['guild_name']['lead'];
                                                break;
                                            case GAMEMASTER:
                                                $permessi_utente = $PARAMETERS['names']['master']['sing'];
                                                break;
                                            case MODERATOR:
                                                $permessi_utente = $PARAMETERS['names']['moderators']['sing'];
                                                break;
                                            case SUPERUSER:
                                                $permessi_utente = $PARAMETERS['names']['administrator']['sing'];
                                                break;
                                        }
                                        echo gdrcd_filter('out',
                                                $permessi_utente) . ' <img src="imgs/icons/permessi' . (int) $record['permessi'] . '.gif" class="profilo_img_gilda" />'; ?>
                                    </div>
                                </div>
                                <?php
                            } ?>


                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['profile']['occupation']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php //carico le gilde
                                    $guilds = gdrcd_query("SELECT ruolo.nome_ruolo, ruolo.gilda, ruolo.immagine, gilda.visibile, gilda.nome AS nome_gilda FROM clgpersonaggioruolo LEFT JOIN ruolo ON ruolo.id_ruolo = clgpersonaggioruolo.id_ruolo LEFT JOIN gilda ON ruolo.gilda = gilda.id_gilda WHERE clgpersonaggioruolo.personaggio = '" . gdrcd_filter('in',
                                            $record['nome']) . "'", 'result');
                                    if (gdrcd_query($guilds, 'num_rows') == 0)
                                    {
                                        echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['profile']['uneployed']);
                                    } else
                                    {
                                        while ($row_guilds = gdrcd_query($guilds, 'fetch'))
                                        {
                                            if ($row_guilds['gilda'] == -1)
                                            {
                                                echo '<img class="profilo_img_gilda"  src="themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/guilds/' . gdrcd_filter('out',
                                                        $row_guilds['immagine']) . '" alt="' . gdrcd_filter('out',
                                                        $row_guilds['nome_ruolo']) . '" title="' . gdrcd_filter('out',
                                                        $row_guilds['nome_ruolo']) . '" />';
                                            } else
                                            {
                                                if (($row_quilds['visibile'] == 1) || ($_SESSION['permessi'] >= USER))
                                                {
                                                    echo '<a href="main.php?page=servizi_gilde&id_gilda=' . $row_guilds['gilda'] . '"><img class="profilo_img_gilda"  src="themes/' . $PARAMETERS['themes']['current_theme'] . '/imgs/guilds/' . gdrcd_filter('out',
                                                            $row_guilds['immagine']) . '" alt="' . gdrcd_filter('out',
                                                            $row_guilds['nome_ruolo'] . ' - ' . $row_guilds['nome_gilda']) . '" title="' . gdrcd_filter('out',
                                                            $row_guilds['nome_ruolo'] . ' - ' . $row_guilds['nome_gilda']) . '" /></a>';
                                                }
                                            }//else
                                        }//while

                                        gdrcd_query($guilds, 'free');

                                    }//else
                                    ?>
                                </div>

                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['race']['sing']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php if ((empty($record['sing_f']) == false) || (empty($record['sing_m']) == false))
                                    {
                                        if ($record['sesso'] == 'f')
                                        {
                                            echo gdrcd_filter('out', $record['sing_f']);
                                        } else
                                        {
                                            echo gdrcd_filter('out', $record['sing_m']);
                                        }
                                    } else
                                    {
                                        echo gdrcd_filter('out',
                                            $PARAMETERS['names']['race']['sing'] . ' ' . $MESSAGE['interface']['sheet']['profile']['no_race']);
                                    } ?>
                                </div>
                            </div>


                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['profile']['experience']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out', $px_totali_pg); ?>
                                </div>
                            </div>
                            <!-- caratteristiche -->
                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car0'] + $record['bonus_car0'] + $bonus_oggetti['BO0']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car1'] + $record['bonus_car1'] + $bonus_oggetti['BO1']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car2'] + $record['bonus_car2'] + $bonus_oggetti['BO2']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car3'] + $record['bonus_car3'] + $bonus_oggetti['BO3']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car4'] + $record['bonus_car4'] + $bonus_oggetti['BO4']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out',
                                        $record['car5'] + $record['bonus_car5'] + $bonus_oggetti['BO5']); ?>
                                </div>
                            </div>

                            <div class="profilo_voce">
                                <div class="profilo_voce_label">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['hitpoints']); ?>:
                                </div>
                                <div class="profilo_voce_valore">
                                    <?php echo gdrcd_filter('out', $record['salute']) . '/' . gdrcd_filter('out',
                                            $record['salute_max']); ?>
                                </div>
                            </div>

                            <div class="profilo_status">
                                <div class="profilo_status_label">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['profile']['status']); ?>:
                                </div>
                                <div class="profilo_status_valore">
                                    <?php echo nl2br(gdrcd_filter('out', $record['stato'])); ?>
                                </div>
                            </div>

                        </div>
                        <!-- Punteggi, salute, status, classe, razza. -->


                        <?php if ($PARAMETERS['mode']['skillsystem'] == 'ON')
                        { //solo se è attiva la modalità skillsystem
                            ?>
                            <div class="elenco_abilita"><!-- Elenco abilità -->

                                <div class="titolo_box">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['box_title']['skills']); ?>
                                </div>

                                <?php
                                //conteggio le abilità
                                $row = gdrcd_query("SELECT COUNT(*) FROM abilita WHERE id_razza=-1 OR id_razza= " . $record['id_razza'] . "");
                                $num = $row['COUNT(*)'];

                                //carico l'elenco delle abilità
                                $result = gdrcd_query("SELECT nome, car, id_abilita FROM abilita WHERE id_razza=-1 OR id_razza= " . $record['id_razza'] . " ORDER BY id_razza DESC, nome",
                                    'result');
                                $count = 0;
                                $total = 0; ?>

                                <div class="form_info"><?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['avalaible_xp']) . ': ' . ($px_totali_pg - $px_spesi); ?></div>
                                <div class="div_colonne_abilita_scheda">
                                    <table class="colonne_abilita_scheda">
                                        <tr>
                                            <?php while ($row = gdrcd_query($result, 'fetch'))
                                            {
                                            if ($count == 0)
                                            {
                                                echo '<td><table>';
                                            } ?>
                                        <tr>
                                            <td>
                                                <div class="abilita_scheda_nome"><?php echo gdrcd_filter('out',
                                                        $row['nome']); ?></div>
                                            </td>
                                            <td>
                                                <div class="abilita_scheda_car">
                                                    <?php echo '(' . gdrcd_filter('out',
                                                            $PARAMETERS['names']['stats']['car' . $row['car']]) . ')'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="abilita_scheda_tank"><?php echo 0 + gdrcd_filter('out',
                                                            $ranks[$row['id_abilita']]); ?></div>
                                            </td>
                                            <td>
                                                <div class="abilita_scheda_sub">
                                                    <?php /*Stampo il form di incremento se il pg ha abbastanza px*/
                                                    if ((((($ranks[$row['id_abilita']] + 1) * $PARAMETERS['settings']['px_x_rank']) <= ($px_totali_pg - $px_spesi)) &&
                                                            (gdrcd_filter('get',
                                                                    $_REQUEST['pg']) == $_SESSION['login']) &&
                                                            ($ranks[$row['id_abilita']] < $PARAMETERS['settings']['skills_cap'])) ||
                                                        ($_SESSION['permessi'] >= MODERATOR)
                                                    )
                                                    { ?>
                                                        [<a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                                                        $_REQUEST['pg']) ?>&op=addskill&what=<?php echo $row['id_abilita'] ?>">+</a>]
                                                        <?php if (($_SESSION['permessi'] >= MODERATOR) &&
                                                        ($ranks[$row['id_abilita']] > 0)
                                                    )
                                                    { ?>
                                                        [
                                                        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                                                            $_REQUEST['pg']) ?>&op=subskill&what=<?php echo $row['id_abilita'] ?>">-</a>]
                                                    <?php } ?>
                                                    <?php } else
                                                    {
                                                        echo '&nbsp;';
                                                    } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $count++;
                                        $total++;
                                        if (($count >= ceil($num / 2)) || ($total >= $num))
                                        {
                                            $count = 0;
                                            echo '</table></td>';
                                        }
                                        }//while

                                        gdrcd_query($result, 'free');
                                        ?>
                                        </tr>
                                    </table>
                                </div>
                                <div class="form_info"><?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['info_skill_cost']); ?></div>

                            </div><!-- Elenco abilità -->
                        <?php } ?>

                        <div class="background"><!-- Background, affetti, robe varie -->

                            <div class="titolo_box">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['box_title']['background']); ?>
                            </div>

                            <div class="body_box">
                                <?php

                                /** * Html, bbcode o entrambi ?
                                 * @author Blancks
                                 */
                                if ($PARAMETERS['mode']['user_bbcode'] == 'ON')
                                {
                                    if ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON')
                                    {
                                        echo bbdecoder(gdrcd_html_filter($record['descrizione']), true);

                                    } elseif ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd')
                                    {
                                        echo bbdecoder(gdrcd_filter('out', $record['descrizione']), true);

                                    } else
                                    {
                                        echo gdrcd_bbcoder(gdrcd_filter('out', $record['descrizione']));
                                    }

                                } else
                                {
                                    echo gdrcd_html_filter($record['descrizione']);
                                }

                                ?>
                            </div>

                        </div>
                        <!-- Background, affetti, robe varie -->

                        <div class="background"><!-- Background, affetti, robe varie -->

                            <div class="titolo_box">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['box_title']['relationships']); ?>
                            </div>

                            <div class="body_box">
                                <?php

                                /** * Html, bbcode o entrambi ?
                                 * @author Blancks
                                 */
                                if ($PARAMETERS['mode']['user_bbcode'] == 'ON')
                                {
                                    if ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON')
                                    {
                                        echo bbdecoder(gdrcd_html_filter($record['affetti']), true);

                                    } elseif ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd')
                                    {
                                        echo bbdecoder(gdrcd_filter('out', $record['affetti']), true);

                                    } else
                                    {
                                        echo gdrcd_bbcoder(gdrcd_filter('out', $record['affetti']));
                                    }

                                } else
                                {
                                    echo gdrcd_html_filter($record['affetti']);
                                }
                                ?>
                            </div>

                        </div>
                        <!-- Background, affetti, robe varie -->

                        <!--- AREA ADMIN --->
                        <?php if ($_SESSION['permessi'] >= MODERATOR)
                        { ?>
                            <div class="log_report">

                                <?php /*report*/ ?>

                            </div>
                        <?php } ?>
                    </div>
                    <?php
                } else
                { ?>
                    <!-- Link a piè di pagina -->
                    <div class="link_back">
                        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                            $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                                $MESSAGE['interface']['sheet']['link']['back']); ?></a>
                    </div>
                <?php }//else
=======
  </div>

  <div class="ritratto_avatar">
     <img src="<?php echo gdrcd_filter('fullurl',$record['url_img']); ?>" class="ritratto_avatar_immagine" />
  </div>

  <div class="iscritto_da">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['first_login']).' '.gdrcd_format_date($record['data_iscrizione']); ?>
  </div>
  <?php if (gdrcd_format_date($record['ora_entrata'])!='00/00/0000'){ ?>
  <div class="ultimo_ingresso">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['last_login']).' '.gdrcd_format_date($record['ora_entrata']); ?>
  </div>
  <?php } ?>


  <div class="ritratto_invia_messaggio"><!-- Link invia messaggio -->
     <a href="main.php?page=messages_center&newmessage=yes&reply_dest=<?php echo gdrcd_filter('url',$record['nome']); ?>" class="link_invia_messaggio">
     <?php if (empty($PARAMETERS['names']['private_message']['image_file'])===FALSE){ ?>
              <img src="<?php echo $PARAMETERS['names']['private_message']['image_file']; ?>"
			       alt="<?php  echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['send']).' '. gdrcd_filter('out',$PARAMETERS['names']['private_message']['sing']).' '.gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out',$record['nome']); ?>"
				   title="<?php  echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['send']).' '. gdrcd_filter('out',$PARAMETERS['names']['private_message']['sing']).' '.gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out',$record['nome']); ?>"
				   class="link_messaggio_forum">
	 <?php } else {
		      echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['send']).' '.gdrcd_filter('out', strtolower($PARAMETERS['names']['private_message']['sing'])).' '.gdrcd_filter('out',$MESSAGE['interface']['sheet']['send_message_to']['to']).' '.gdrcd_filter('out',$record['nome']);
	       } ?>
	  </a>
   </div><!-- Link invia messaggio -->

</div><!-- nome, ritratto, ultimo ingresso, abiti portati -->



<div class="profilo"><!-- Punteggi, salute, status, classe, razza. -->

  <div class="titolo_box">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['box_title']['profile']); ?>
  </div>

  <?php if($record['permessi']>0){ ?>
  <div class="profilo_voce">
     <div class="profilo_voce_label">
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['profile']['role']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php
	        switch ($record['permessi']){
		       case USER: $permessi_utente = ''; break;
		       case GUILDMODERATOR: $permessi_utente = $PARAMETERS['names']['guild_name']['lead']; break;
 		       case GAMEMASTER: $permessi_utente =  $PARAMETERS['names']['master']['sing']; break;
		       case MODERATOR: $permessi_utente = $PARAMETERS['names']['moderators']['sing']; break;
		       case SUPERUSER: $permessi_utente = $PARAMETERS['names']['administrator']['sing']; break;
            }
            echo gdrcd_filter('out',$permessi_utente).' <img src="imgs/icons/permessi'.(int)$record['permessi'].'.gif" class="profilo_img_gilda" />';	    ?>
	 </div>
  </div>
  <?php
} ?>


  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['profile']['occupation']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	 <?php //carico le gilde
		 $guilds=gdrcd_query("SELECT ruolo.nome_ruolo, ruolo.gilda, ruolo.immagine, gilda.visibile, gilda.nome AS nome_gilda FROM clgpersonaggioruolo LEFT JOIN ruolo ON ruolo.id_ruolo = clgpersonaggioruolo.id_ruolo LEFT JOIN gilda ON ruolo.gilda = gilda.id_gilda WHERE clgpersonaggioruolo.personaggio = '".gdrcd_filter('in',$record['nome'])."'", 'result');
		 if (gdrcd_query($guilds, 'num_rows')==0){
		    echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['profile']['uneployed']);
		 }else{
		   while ($row_guilds = gdrcd_query($guilds, 'fetch')){
	          if($row_guilds['gilda']==-1){
		          echo '<img class="profilo_img_gilda"  src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/guilds/'.gdrcd_filter('out',$row_guilds['immagine']).'" alt="'.gdrcd_filter('out',$row_guilds['nome_ruolo']).'" title="'.gdrcd_filter('out',$row_guilds['nome_ruolo']).'" />';
	          } else {
				  if(($row_quilds['visibile']==1)||($_SESSION['permessi']>=USER)){
				  echo '<a href="main.php?page=servizi_gilde&id_gilda='.$row_guilds['gilda'].'"><img class="profilo_img_gilda"  src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/guilds/'.gdrcd_filter('out',$row_guilds['immagine']).'" alt="'.gdrcd_filter('out',$row_guilds['nome_ruolo'].' - '.$row_guilds['nome_gilda']).'" title="'.gdrcd_filter('out',$row_guilds['nome_ruolo'].' - '.$row_guilds['nome_gilda']).'" /></a>';
				  }
			  }//else
		    }//while

		    gdrcd_query($guilds, 'free');

         }//else?>
	 </div>

  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['race']['sing']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php if((empty($record['sing_f'])==FALSE)||(empty($record['sing_m'])==FALSE)){
	             if($record['sesso']=='f'){echo gdrcd_filter('out',$record['sing_f']);}
                 else{echo gdrcd_filter('out',$record['sing_m']);}
              } else { echo gdrcd_filter('out',$PARAMETERS['names']['race']['sing'].' '.$MESSAGE['interface']['sheet']['profile']['no_race']);}?>
	 </div>
  </div>



  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['profile']['experience']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$px_totali_pg); ?>
	 </div>
  </div>
  <!-- caratteristiche -->
  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car0']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car0']+$record['bonus_car0']+$bonus_oggetti['BO0']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car1']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car1']+$record['bonus_car1']+$bonus_oggetti['BO1']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car2']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car2']+$record['bonus_car2']+$bonus_oggetti['BO2']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car3']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car3']+$record['bonus_car3']+$bonus_oggetti['BO3']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car4']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car4']+$record['bonus_car4']+$bonus_oggetti['BO4']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['car5']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['car5']+$record['bonus_car5']+$bonus_oggetti['BO5']); ?>
	 </div>
  </div>

  <div class="profilo_voce">
     <div class="profilo_voce_label">
	    <?php echo gdrcd_filter('out',$PARAMETERS['names']['stats']['hitpoints']); ?>:
	 </div>
	 <div class="profilo_voce_valore">
	    <?php echo gdrcd_filter('out',$record['salute']).'/'.gdrcd_filter('out',$record['salute_max']); ?>
	 </div>
  </div>

  <div class="profilo_status">
     <div class="profilo_status_label">
	    <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['profile']['status']); ?>:
	 </div>
	 <div class="profilo_status_valore">
	    <?php echo nl2br(gdrcd_filter('out',$record['stato'])); ?>
	 </div>
  </div>

</div><!-- Punteggi, salute, status, classe, razza. -->


<?php if($PARAMETERS['mode']['skillsystem']=='ON'){ //solo se è attiva la modalità skillsystem?>
<div class="elenco_abilita"><!-- Elenco abilità -->

  <div class="titolo_box">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['box_title']['skills']); ?>
  </div>

<?php
  //conteggio le abilità
  $row=gdrcd_query("SELECT COUNT(*) FROM abilita WHERE id_razza=-1 OR id_razza= ".$record['id_razza']."");
  $num=$row['COUNT(*)'];

  //carico l'elenco delle abilità
  $result=gdrcd_query("SELECT nome, car, id_abilita FROM abilita WHERE id_razza=-1 OR id_razza= ".$record['id_razza']." ORDER BY id_razza DESC, nome", 'result');
  $count=0;
  $total=0;?>

  <div class="form_info"><?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['avalaible_xp']).': <span id="skillpoints">'. ($px_totali_pg-$px_spesi) .'</span>'; ?></span></div>
  <div class="div_colonne_abilita_scheda">
  <table class="colonne_abilita_scheda"><tr>
  <?php while($row=gdrcd_query($result, 'fetch')){
  if ($count==0){echo '<td><table>';}?>
  <tr>
  <td><div class="abilita_scheda_nome"><?php echo gdrcd_filter('out',$row['nome']); ?></div></td>
  <td>
     <div class="abilita_scheda_car">
	    <?php echo '('.gdrcd_filter('out',$PARAMETERS['names']['stats']['car'.$row['car']]).')'; ?>
	 </div>
  </td>
  <td><div class="abilita_scheda_tank"><span class="skill" data-skillcurvalue="<?php echo 0+gdrcd_filter('out',$ranks[$row['id_abilita']]); ?>" data-skillid="<?php echo $row['id_abilita'] ?>"><?php echo 0+gdrcd_filter('out',$ranks[$row['id_abilita']]); ?></span></div></td>
  <td>
     <div class="abilita_scheda_sub">
        <?php /*Stampo il form di incremento se il pg ha abbastanza px*/
              if((((($ranks[$row['id_abilita']]+1)*$PARAMETERS['settings']['px_x_rank'])<=($px_totali_pg-$px_spesi))&&
                 (gdrcd_filter('get',$_REQUEST['pg'])==$_SESSION['login'])&&
                 ($ranks[$row['id_abilita']]<$PARAMETERS['settings']['skills_cap']))||
                 ($_SESSION['permessi']>=MODERATOR)){ ?>
                 <a href="javascript:void(0);" class="skill_incr" data-skillid="<?php echo $row['id_abilita'] ?>">[+]</a>
                 <a href="javascript:void(0);" class="skill_decr" data-skillid="<?php echo $row['id_abilita'] ?>">[-]</a>
        <?php } else { echo '&nbsp;';} ?>
     </div>
  </td>
  </tr>
  <?php $count++; $total++;
  if (($count>=ceil($num/2))||($total>=$num)){$count=0; echo '</table></td>';}
  }//while

  gdrcd_query($result, 'free');
  ?>
  </tr>
  </table>
  </div>
  
  <form action="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',$_REQUEST['pg']) ?>" method="post">
    <input type="hidden" name="op" value="saveskills">
    <input type="hidden" id="skillsinput" name="skills" value="">
    <button id="saveskills" disabled>Salva Modifiche!</button>
  </form>
  
  <div class="form_info"><?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['info_skill_cost']);?></div>

</div><!-- Elenco abilità -->
<script type="text/javascript">

    var gdrcdSkills = new gdrcdSkills(
        '#skillpoints', 
        '.skill', 
        '.skill_incr', 
        '.skill_decr',
        function(rank) {
            return <?php echo gdrcd_filter('num', $PARAMETERS['settings']['px_x_rank']); ?> * rank;
        }
    );
    
    <?php if ($_SESSION['permessi']>=MODERATOR) { ?>

        gdrcdSkills.allowMasterDecrement(true);

    <?php } ?>
    
    var skillsInput = document.getElementById('skillsinput'),
        skillsSaveButton = document.getElementById('saveskills'),
        defaultSkillsValue = gdrcdSkills.exportSkillsValue();
    
    gdrcdSkills.onSkillChange(function() {
        var newSkillsValue = gdrcdSkills.exportSkillsValue();
        skillsInput.value = newSkillsValue;
                
        if (newSkillsValue !== defaultSkillsValue) {
            skillsSaveButton.removeAttribute('disabled');
        } else {
            skillsSaveButton.setAttribute('disabled', '');
        }
    });
    
</script>
<?php } ?>

<div class="background"><!-- Background, affetti, robe varie -->

  <div class="titolo_box">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['box_title']['background']); ?>
  </div>

  <div class="body_box">
     <?php

		/** * Html, bbcode o entrambi ?
			* @author Blancks
		*/
		if ($PARAMETERS['mode']['user_bbcode'] == 'ON')
		{
			if ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON')
			{
				echo bbdecoder(gdrcd_html_filter($record['descrizione']), true);

			}elseif ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd')
			{
				echo bbdecoder(gdrcd_filter('out',$record['descrizione']), true);

			}else
			{
				echo gdrcd_bbcoder(gdrcd_filter('out',$record['descrizione']));
			}

		}else
		{
			echo gdrcd_html_filter($record['descrizione']);
		}

     ?>
  </div>

</div><!-- Background, affetti, robe varie -->

<div class="background"><!-- Background, affetti, robe varie -->

  <div class="titolo_box">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['box_title']['relationships']); ?>
  </div>

  <div class="body_box">
     <?php

		/** * Html, bbcode o entrambi ?
			* @author Blancks
		*/
		if ($PARAMETERS['mode']['user_bbcode'] == 'ON')
		{
			if ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd' && $PARAMETERS['settings']['bbd']['free_html'] == 'ON')
			{
				echo bbdecoder(gdrcd_html_filter($record['affetti']), true);

			}elseif ($PARAMETERS['settings']['user_bbcode']['type'] == 'bbd')
			{
				echo bbdecoder(gdrcd_filter('out',$record['affetti']), true);

			}else
			{
				echo gdrcd_bbcoder(gdrcd_filter('out',$record['affetti']));
			}

		}else
		{
			echo gdrcd_html_filter($record['affetti']);
		}
	?>
  </div>

</div><!-- Background, affetti, robe varie -->

<!--- AREA ADMIN --->
<?php if($_SESSION['permessi']>=MODERATOR){ ?>
<div class="log_report">

   <?php /*report*/ ?>

</div>
<?php } ?>
</div>
<?php
} else { ?>
<!-- Link a piè di pagina -->
<div class="link_back">
   <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',$_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['link']['back']); ?></a>
</div>
<?php }//else
>>>>>>> master

            }//else
            /********* CHIUSURA SCHEDA **********/
        }//else

        ?>

    <?php }//else?>

    <!-- embed src="<?php //echo gdrcd_bbcoder(gdrcd_filter('out',$record['url_media'])); ?>" height="0" width="0"-->

    <?php
    //Impedisci XSS nella musica
    $record['url_media'] = gdrcd_filter('fullurl', $record['url_media']);
    if ($PARAMETERS['mode']['allow_audio'] == 'ON' && ! $_SESSION['blocca_media'] && ! empty($record['url_media']))
    {
        ?>


        <object data="<?php echo $record['url_media']; ?>"
                type="<?php echo $PARAMETERS['settings']['audiotype']['.' . strtolower(end(explode('.',
                    $record['url_media'])))]; ?>" autostart="true">
            <embed src="<?php echo $record['url_media']; ?>" autostart="true" hidden="true"/>
        </object>

        <!--[if IE9]>
        <embed src="<?php echo $record['url_media']; ?>" autostart="true" hidden="true"/>
        <![endif]-->


        <?php

    }

    ?>
</div><!-- Pagina -->