<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = NewsTipo::getInstance(); # Inizializzo classe

if ( $cls->permissionManageNewsType() ) { # Metodo di controllo per accesso alla pagina di gestione
    if ( News::getInstance()->newsEnabled() ) {
        ?>
        <div class="general_incipit">
            <div class="title"> Gestione Tipologie News</div>
            <div class="subtitle">Gestione delle tipologie di news</div>

            Da questa pagina è possibile:
            <ul>
                <li>Creare una nuova tipologia news</li>
                <li>Modificare una tipologia news esistente</li>
                <li>Eliminare una tipologia news</li>
            </ul>

        </div>

        <div class="form_container manage_news_type_container">

            <!-- INSERT -->
            <form class="form ajax_form" action="gestione/news/tipo/gestione_news_tipo_ajax.php"
                  data-callback="updateNewsTypes">

                <div class="form_title">Crea Tipologia News</div>

                <div class="single_input">
                    <div class="label">Nome</div>
                    <input type="text" name="nome">
                </div>

                <div class="single_input">
                    <div class="label">Descrizione</div>
                    <textarea name="descrizione"></textarea>
                </div>

                <div class="single_input">
                    <div class="label">Attiva</div>
                    <input type="checkbox" name="attiva">
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                    <input type="submit" value="Crea">
                </div>

            </form>

            <!-- EDIT -->
            <form class="form ajax_form edit_form" action="gestione/news/tipo/gestione_news_tipo_ajax.php"
                  data-callback="updateNewsTypes">

                <div class="form_title">Modifica Tipologia News</div>

                <div class="single_input">
                    <div class="label">Tipologia News</div>
                    <select name="id" required>
                        <?= $cls->listNewsType(false); ?>
                    </select>
                </div>

                <div class="single_input">
                    <div class="label">Nome</div>
                    <input type="text" name="nome">
                </div>

                <div class="single_input">
                    <div class="label">Descrizione</div>
                    <textarea name="descrizione"></textarea>
                </div>

                <div class="single_input">
                    <div class="label">Attiva</div>
                    <input type="checkbox" name="attiva">
                </div>


                <div class="single_input">
                    <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                    <input type="submit" value="Modifica">
                </div>

            </form>

            <!-- DELETE -->
            <form class="form ajax_form" action="gestione/news/tipo/gestione_news_tipo_ajax.php"
                  data-callback="updateNewsTypes">

                <div class="form_title">Elimina una Tipologia News</div>

                <div class="single_input">
                    <div class="label">Tipologia News</div>
                    <select name="id" required>
                        <?= $cls->listNewsType(false); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                    <input type="submit" value="Elimina">
                </div>

            </form>

        </div>

        <script src="<?= Router::getPagesLink('gestione/news/tipo/gestione_news_tipo.js'); ?>"></script>
        <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


    <?php } else { ?>
        <div class="warning error">News Disattivate.</div>
    <?php }
} else { ?>
    <div class="warning error">Permesso negato.</div>
<?php } ?>
