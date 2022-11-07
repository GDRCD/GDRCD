<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Forum::getInstance(); # Inizializzo classe

if ( ForumPermessi::getInstance()->permissionForumAdmin() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Forum Permessi</div>
        <div class="subtitle">Gestione dei permessi di visualizzazione da assegnare agli utenti per la visione di un determinato forum</div>
        <br/>

        Da questa pagina è possibile:
        <ul>
            <li>Assegnare la visualizzazione di un forum a un pg</li>
            <li>Rimuovere la visualizzazione di un forum a un pg, dopo averlo trovato dall'apposito form</li>
        </ul>

        <br/>
        Inoltre è possibile visualizzare i permessi precedentemente assegnati, filtrandoli per:
        <ul>
            <li>Tutti - Non selezionando nessun menu a tendina</li>
            <li>PG - Utilizzando il menu a tendina "Personaggio"</li>
            <li>Forum - Utilizzando il menu a tendina "Forum"</li>
        </ul>

    </div>

    <div class="form_container manage_forums_permissions">

        <!-- ASSIGN -->
        <form class="form ajax_form" action="gestione/forum/permessi/gestione_forum_permessi_ajax.php"
              data-callback="submitForumVisual">

            <div class="form_title">Assegna permesso forum</div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio" required>
                    <?= Functions::getPgList(); ?>
                </select>
            </div>


            <div class="single_input">
                <div class="label">Forum</div>
                <select name="forum" required>
                    <?= $cls->listForums();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>


        <!-- VISUALIZZA -->
        <form class="form ajax_form visual_form" action="gestione/forum/permessi/gestione_forum_permessi_ajax.php"
              data-callback="updateForumPermission" data-swal="false" data-reset="false">

            <div class="form_title">Cerca permessi esistenti</div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio">
                    <?= Functions::getPgList(); ?>
                </select>
            </div>


            <div class="single_input">
                <div class="label">Forum</div>
                <select name="forum">
                    <?= $cls->listForums();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="get_permissions"> <!-- OP NEEDED -->
                <input type="submit" value="Cerca">
            </div>

        </form>

        <div class="fake-table visual_result">

        </div>

    </div>

    <script src="<?= Router::getPagesLink('gestione/forum/permessi/gestione_forum_permessi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
