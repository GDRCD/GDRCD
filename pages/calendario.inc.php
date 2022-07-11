<div class="pagina_calendario">


    <div class="calendario">
        <div id='calendar-container'>
            <div id='calendar'></div>
        </div>
    </div>
    <div class="eventi">
        <?php

        # Richieste Post
        switch (gdrcd_filter_get($_POST['op'])) {

            case 'edit': //Form modifica pagina
                include('calendario/edit.php');
                break;

            case 'new': // Form nuova pagina
                include('calendario/new.php');
                break;

            case 'save_edit': // Salvataggio modifiche
            case 'delete': // Eliminazione
            case 'save_new': //Inserimento nuova pagina
                include('calendario/save.php');
                break;

            default: //Lista pagine
                include('calendario/index.php');
                break;
        }  ?>
    </div>
    <div style="clear:both;"></div>
    <div class="area_bottoni">
        <form action="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario" method="post">
            <button type="submit"   class="button" >Visualizza eventi</button>
        </form>

        <?php
        if($_SESSION['permessi']>=MODERATOR){
            ?>
            <form action="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario" method="post">
                <input hidden value="new" name="op">
                <button type="submit"   class="button" >Aggiungi</button>
            </form>
            <form action="<?php echo (CALENDAR_POPUP)?'popup' : 'main'; ?>.php?page=calendario" method="post">
                <input hidden value="edit" name="op">
                <button type="submit"   class="button" >Modifica</button>
            </form>

            <?php
        }
        ?>


    </div>



</div>

<script src='pages/calendario/calendar.js'></script>