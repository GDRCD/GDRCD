<?php

Router::loadRequired(); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = ChatOpzioni::getInstance(); # Inizializzo classe

if ($cls->permissionManageOptions()) { # Metodo di controllo per accesso alla pagina di gestione
    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Opzioni Chat</div>
        <br>
        <div class="subtitle">Gestione delle opzioni di customizzazione della chat.</div>
        <br>
        Da questa pagina è possibile:
        <ul>
            <li>Creare le customizzazioni presenti nella scheda personaggio</li>
            <li>Modificare le customizzazioni presenti nella scheda personaggio</li>
            <li>Rimuovere le customizzazioni presenti nella scheda personaggio</li>
        </ul>
        <br>

        E' importante sapere che i valori creati qui si auto-inseriscono nell'apposita pagina di modifica opzioni chat della scheda,
        ma che vanno poi collegate,
        all'interno del codice, nel punto interessato.
    </div>


    <div class="form_container gestione_chat_opzioni">

        <!-- INSERT -->
        <form class="form ajax_form"
              action="gestione/chat/opzioni/gestione_chat_opzioni_ajax.php"
              data-callback="updateOptionsList">

            <div class="form_title">Aggiunta opzione chat</div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" required>
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione" required></textarea>
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="tipo" id="tipo" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form class="form ajax_form edit-form"
              action="gestione/chat/opzioni/gestione_chat_opzioni_ajax.php"
              data-callback="updateOptionsList">

            <div class="form_title">Modifica opzione chat</div>

            <div class="single_input">
                <div class="label">Opzione</div>
                <select name="id" required>
                    <?= $cls->listOptions(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Nome</div>
                <input type="text" name="nome" required>
            </div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" required>
            </div>

            <div class="single_input">
                <div class="label">Descrizione</div>
                <textarea name="descrizione" required></textarea>
            </div>

            <div class="single_input">
                <div class="label">Tipo</div>
                <select name="tipo" id="tipo" required>
                    <?= $cls->listTypes(); ?>
                </select>
            </div>


            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form class="form ajax_form"
              action="gestione/chat/opzioni/gestione_chat_opzioni_ajax.php"
              data-callback="updateOptionsList">

            <div class="form_title">Elimina opzione chat</div>

            <div class="single_input">
                <div class="label">Opzione</div>
                <select name="id" required>
                    <?= $cls->listOptions(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>


        <div class="link_back">
            <a href="/main.php?page=gestione">Torna indietro</a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('gestione/chat/opzioni/gestione_chat_opzioni.js'); ?>"></script>


<?php } ?>
