<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Forum::getInstance(); # Inizializzo classe

if ( ForumPermessi::getInstance()->permissionForumAdmin() ) { # Metodo di controllo per accesso alla pagina di gestione

    ?>
    <div class="general_incipit">
        <div class="title"> Gestione Forum</div>
        <div class="subtitle">Gestione dei forum presenti</div>

        Da questa pagina è possibile:
        <ul>
            <li>Creare un nuovo forum</li>
            <li>Modificare un forum esistente</li>
            <li>Eliminare un forum</li>
        </ul>

    </div>

    <div class="form_container manage_forums">

        <!-- INSERT -->
        <form class="form ajax_form" action="gestione/forum/forum/gestione_forum_ajax.php"
              data-callback="updateForums">

            <div class="form_title">Crea Forum</div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="tipo" required>
                    <?= ForumTipo::getInstance()->listTypes(); ?>
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

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit_form" action="gestione/forum/forum/gestione_forum_ajax.php"
              data-callback="updateForums">

            <div class="form_title">Modifica forum</div>

            <div class="single_input">
                <div class="label">Forum</div>
                <select name="id" required>
                    <?= $cls->listForums(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="tipo" required>
                    <?= ForumTipo::getInstance()->listTypes(); ?>
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


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form" action="gestione/forum/forum/gestione_forum_ajax.php"
              data-callback="updateForums">

            <div class="form_title">Elimina un forum</div>

            <div class="single_input">
                <div class="label">Forum</div>
                <select name="id" required>
                    <?= $cls->listForums(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="<?= Router::getPagesLink('gestione/forum/forum/gestione_forum.js'); ?>"></script>
    <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


<?php } ?>
