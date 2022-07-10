<?php
$pg = $_REQUEST['pg'];
if ($_REQUEST['pg'] == $_SESSION['login']) {
    $mesedopo = $_POST['mese']+1;
    $meseprima=$_POST['mese']-1;
    ?>
    <div style="overflow:auto; margin:auto;text-align:center;">
        <div class="form_info">In questo pannello puoi registrare le giocate che hai dimenticato di segnare in chat!
            E' necessario inserire la chat in cui si è giocato, l'orario ed i dettagli importanti della giocata.
            Orario e chat saranno verificati per controllare che ci siano almeno 5 azioni per giocata.
            In caso di margini orari troppo larghi, sarà salvato tutto quel che c'è fra la prima e
            l'ultima azione del personaggio.
        </div><br>
        <!-- Chat -->
        <?php
        $chat=gdrcd_query("SELECT nome, id FROM mappa WHERE chat=1 ORDER BY nome", 'result'); ?>
        <form action="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in',$_REQUEST['pg']); ?>"  method="post">
            <div class="titolo_box" >Seleziona la <b>chat</b> di gioco</div>
            <div class='form_field' style="margin:auto;">
                <select name="luogo">
                    <?php while($r_chat=gdrcd_query($chat, 'fetch')){?>
                        <option value="<?php echo gdrcd_filter('out',$r_chat['id']); ?>" >
                            <?php echo  gdrcd_filter('out',$r_chat['nome']); ?>
                        </option>
                    <?php }//while

                    gdrcd_query($chat, 'free');
                    ?>
                </select>
            </div><br>

            <div class='form_field'>
                <!-- Giorno -->
                <div class="titolo_box">Seleziona la <b>data di inizio</b> giocata</div>
                <br>
                Giorno:  <select name="day_a" class="day">
                    <?php for($i=1; $i<=31; $i++){?>
                        <option value="<?php echo $i;?>"><?php echo $i;?></option>
                    <?php }//for ?>
                </select> | <select name="month_a" class="day">
                    <?php if ($_POST['mese']!==1) { ?>
                        <option value="<?php echo $meseprima;?>"><?php echo $meseprima;?></option>
                    <?php } ?>
                    <option value="<?php echo $_POST['mese'];?>" selected ><?php echo $_POST['mese'];?></option>
                    <?php if ($_POST['mese']!==12) { ?>
                        <option value="<?php echo $mesedopo;?>"><?php echo $mesedopo;?></option>
                    <?php } ?>
                </select> | <?php echo $_POST['anno']; ?><br>
                <!-- Ora -->
                Ora:<select name="hour_a" class="month">
                    <?php for($i=0; $i<=23; $i++){?>
                        <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>:
                <!-- Minuto -->
                <select name="minut_a" class="month">
                    <?php for($i=0; $i<=60; $i+=5){?>
                        <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>
            </div><br>

            <div class='form_field'>
                <!-- Giorno -->
                <div class="titolo_box">Seleziona la <b>data di fine</b> giocata</div>
                <br>
                Giorno:  <select name="day_b" class="day">
                    <?php for($i=1; $i<=31; $i++){?>
                        <option value="<?php echo $i;?>"><?php echo $i;?></option>
                    <?php }//for ?>
                </select> | <select name="month_b" class="day">
                    <?php if ($_POST['mese']!==1) { ?>
                        <option value="<?php echo $meseprima;?>"><?php echo $meseprima;?></option>
                    <?php } ?>
                    <option value="<?php echo $_POST['mese'];?>" selected ><?php echo $_POST['mese'];?></option>
                    <?php if ($_POST['mese']!==12) { ?>
                        <option value="<?php echo $mesedopo;?>"><?php echo $mesedopo;?></option>
                    <?php } ?>
                </select> | <?php echo $_POST['anno']; ?><br>
                <!-- Ora -->
                Ora:  <select name="hour_b" class="month">
                    <?php for($i=0; $i<=23; $i++){?>
                        <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>:
                <!-- Minuto -->
                <select name="minut_b" class="month">
                    <?php for($i=0; $i<=60; $i+=5){?>
                        <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>
            </div>
            <br>
            <div class='form_field'>
                <div class="titolo_box"> Inserisci dei <b>tag</b> che riassumano la giocata:</div>
               <input name="ab" type="text" style="margin: auto;" value="" />
            </div>
            <div class="form_field">I tag possono essere utili per ritrovare rapidamente una role.</div>
            <div class="titolo_box"> Note di trama</div>
            <input name="quest" type="text" style="margin: auto;" value="" />
            <div class="form_info">Compilare con un brevissimo riassunto di cosa fatto in giocata, focalizzandosi sulle interazioni con eventuali spunti di trama. </center></div>
            <br>
                <!--- registrazione giocate ---->
                <div class="form_submit">
                    <input type="hidden"
                           name="op"
                           value="send_segn" />
                    <input type="hidden"
                           name="mese"
                           value="<?php echo gdrcd_filter('num',$_POST['mese']);?>" />
                    <input type="hidden"
                           name="anno"
                           value="<?php echo gdrcd_filter('num',$_POST['anno']);?>" />
                    <input type="submit"
                           name="submit"
                           value="Registra la giocata" />
                </div>
            </center>
        </form>
    </div>
    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in',$_REQUEST['pg']); ?>">
            <?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
        </a>
    </div>
<?php
} else {
    echo '<div class="warning">Non puoi inserire registrazioni nella scheda altrui</div>';
}
?>
