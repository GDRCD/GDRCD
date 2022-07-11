<div class="pagina_scheda_calendario">
    <?php /*HELP: */
    //Se non e' stato specificato il nome del pg
    if(isset($_REQUEST['pg']) === false) {
        echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
        exit();
    }
    else{
        $pg=gdrcd_filter('out', $_REQUEST['pg']);
    }
    /*Visualizzo la pagina*/
  ?>
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['page_name']); ?></h2>
    </div>

    <!-- Elenco oggetti nello zaino -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['calendar']); ?></h2>
    </div>
    <div class="page_body">
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
                        include('scheda/calendario/edit.php');
                        break;
                    case 'edit_page': //Form modifica pagina
                        include('scheda/calendario/edit_page.php');
                        break;

                    case 'new': // Form nuova pagina
                        include('scheda/calendario/new.php');
                        break;

                    case 'save_edit': // Salvataggio modifiche
                    case 'delete': // Eliminazione
                    case 'save_new': //Inserimento nuova pagina
                        include('scheda/calendario/save.php');
                        break;

                    default: //Lista pagine
                        include('scheda/calendario/index.php');
                        break;
                }  ?>
            </div>
            <div style="clear:both;"></div>
            <div class="area_bottoni">
                <form action="main.php?page=scheda_calendario&pg=<?=$pg?>" method="post">
                    <button type="submit"   class="button" >Visualizza eventi</button>
                </form>

                <?php
                if($_SESSION['permessi']>=MODERATOR){
                    ?>
                    <form action="main.php?page=scheda_calendario&pg=<?=$pg?>" method="post">
                        <input hidden value="new" name="op">
                        <button type="submit"   class="button" >Aggiungi</button>
                    </form>
                    <form action="main.php?page=scheda_calendario&pg=<?=$pg?>" method="post">
                        <input hidden value="edit" name="op">
                        <button type="submit"   class="button" >Modifica</button>
                    </form>

                    <?php
                }
                ?>


            </div>
        </div>
        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
        </div>
    </div>
</div><!-- Pagina -->


<script src='pages/scheda/calendario/calendar.js'></script>