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
        } ?>
    </div>
    <div style="clear:both;"></div>
    <div class="area_bottoni">
        <a href="popup.php?page=calendario" class="button" >Visualizza appuntamenti</a>


    </div>



</div>

<script src='pages/calendario/calendar.js'></script>