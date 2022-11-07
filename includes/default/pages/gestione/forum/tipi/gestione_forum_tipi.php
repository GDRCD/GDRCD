<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = ForumTipo::getInstance(); # Inizializzo classe

if ( ForumPermessi::getInstance()->permissionForumAdmin() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Forum Tipi</div>
        <div class="subtitle">Gestione dei tipi di forum presenti</div>

        Da questa pagina è possibile:
        <ul>
            <li>Creare un nuovo tipo di forum</li>
            <li>Modificare un tipo di forum esistente</li>
            <li>Eliminare un tipo di forum</li>
        </ul>

        <br>

        <div class="subtitle_gst">Eliminare un tipo di forum <span class="highlight">NON</span>  elimina anche i forum a esso associato.</div>

    </div>

    <div class="form_container manage_forums_types">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/forum/tipi/gestione_forum_tipi_ajax.php"
              data-callback="updateForumsTypes">

            <div class="form_title">Crea tipologia forum</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <input type="text" name="descrizione">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Pubblico</div>
                <input type="checkbox" name="pubblico">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit_form" action="gestione/forum/tipi/gestione_forum_tipi_ajax.php"
              data-callback="updateForumsTypes">

            <div class="form_title">Modifica tipologia forum</div>

            <div class="single_input">
                <div class="label">Tipologia</div>
                <select name="id" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <input type="text" name="descrizione">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Pubblico</div>
                <input type="checkbox" name="pubblico">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/forum/tipi/gestione_forum_tipi_ajax.php"
              data-callback="updateForumsTypes">

            <div class="form_title">Elimina tipologia forum</div>

            <div class="single_input">
                <div class="label">Tipologia</div>
                <select name="id" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/forum/tipi/gestione_forum_tipi.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
