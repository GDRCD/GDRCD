<?php

Router::loadRequired();# Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Mercato::getInstance(); # Inizializzo classe

if ($cls->manageShopPermission()) { # Metodo di controllo per accesso alla pagina di gestione

    if (isset($_POST['op'])) { # Se ho richiesto un'operazione
        switch ($_POST['op']) { # In base al tipo di operazione eseguo insert/edit/delete/altro

        }
    }


    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Negozi Mercato</div>
        <br>
        <div class="subtitle">Gestione dei negozi presenti nel mercato</div>
        <br>
        La pagina gestisce i negozi presenti nel mercato.
        <br>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Creare un nuovo negozio</li>
            <li>Modificare un negozio</li>
            <li>Rimuovere un negozio</li>
        </ul>
        <br>
        <div class="highlight"> Rimuovere un negozio NON sposta automaticamente gli oggetti al suo interno su un altro negozio e sara' necessario procedere manualmente allo spostamento. </div>
    </div>


    <div class="form_container gestione_negozi">

        <?php if (isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?= $resp['mex']; ?></div>
            <div class="link_back">
                <a href="/main.php?page=gestione_mercato_negozi">
                    Indietro
                </a>
            </div>

            <?php
            Functions::redirect('/main.php?page=gestione_mercato_negozi', 3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form ajax_form" action="gestione/mercato/gestione_mercato_ajax.php" data-callback="refreshShopLists">

            <div class="form_title">Aggiunta negozio</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine" required>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_insert_shop"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form ajax_form" action="gestione/mercato/gestione_mercato_ajax.php"  data-callback="refreshShopLists">

            <div class="form_title">Modifica negozio</div>

            <div class="single_input">
                <div class="label">Negozio</div>
                <select name="id" required>
                    <?=$cls->listShops();?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Immagine</div>
                <input type="text" name="immagine" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit_shop"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form ajax_form" action="gestione/mercato/gestione_mercato_ajax.php"  data-callback="refreshShopLists">

            <div class="form_title">Elimina oggetto da negozio</div>

            <div class="single_input">
                <div class="label">Negozio</div>
                <select name="id" required>
                    <?= $cls->listShops(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_shop"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="<?=Router::getPagesLink('gestione/mercato/gestione_mercato_negozi.js');?>"></script>


<?php } ?>
