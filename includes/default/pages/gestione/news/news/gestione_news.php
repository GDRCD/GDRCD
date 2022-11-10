<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = News::getInstance(); # Inizializzo classe

if ( $cls->permissionManageNewsType() ) { # Metodo di controllo per accesso alla pagina di gestione
    if ( News::getInstance()->isActive() ) {
        ?>
        <div class="general_incipit">
            <div class="title"> Gestione News</div>
            <div class="subtitle">Gestione delle news</div>

            Da questa pagina è possibile:
            <ul>
                <li>Creare una nuova news</li>
                <li>Modificare una news esistente</li>
                <li>Eliminare una news</li>
            </ul>

        </div>

        <div class="form_container manage_news_container">

            <!-- INSERT -->
            <form class="form ajax_form" action="gestione/news/news/gestione_news_ajax.php"
                  data-callback="updateNews">

                <div class="form_title">Crea News</div>

                <div class="single_input">
                    <div class="label">Autore Personalizzato</div>
                    <input type="text" name="autore">
                </div>

                <div class="single_input">
                    <div class="label">Titolo</div>
                    <input type="text" name="titolo">
                </div>

                <div class="single_input">
                    <div class="label">Testo</div>
                    <textarea name="testo"></textarea>
                </div>

                <div class="single_input">
                    <div class="label">Tipo</div>
                    <select name="tipo">
                        <?= NewsTipo::getInstance()->listNewsType(false); ?>
                    </select>
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
            <form class="form ajax_form edit_form" action="gestione/news/news/gestione_news_ajax.php"
                  data-callback="updateNews">

                <div class="form_title">Modifica news</div>

                <div class="single_input">
                    <div class="label">News</div>
                    <select name="id" required>
                        <?= $cls->listNews(false); ?>
                    </select>
                </div>

                <div class="single_input">
                    <div class="label">Autore Personalizzato</div>
                    <input type="text" name="autore">
                </div>

                <div class="single_input">
                    <div class="label">Titolo</div>
                    <input type="text" name="titolo">
                </div>

                <div class="single_input">
                    <div class="label">Testo</div>
                    <textarea name="testo"></textarea>
                </div>

                <div class="single_input">
                    <div class="label">Tipo</div>
                    <select name="tipo">
                        <?= NewsTipo::getInstance()->listNewsType(false); ?>
                    </select>
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
            <form class="form ajax_form" action="gestione/news/news/gestione_news_ajax.php"
                  data-callback="updateNews">

                <div class="form_title">Elimina una news</div>

                <div class="single_input">
                    <div class="label">News</div>
                    <select name="id" required>
                        <?= $cls->listNews(false); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                    <input type="submit" value="Elimina">
                </div>

            </form>

        </div>

        <script src="<?= Router::getPagesLink('gestione/news/news/gestione_news.js'); ?>"></script>
        <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>


    <?php } else { ?>
        <div class="warning error">News Disattivate.</div>
    <?php }
} else { ?>
    <div class="warning error">Permesso negato.</div>
<?php } ?>