<?php

require_once(__DIR__.'/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Oggetti::getInstance(); # Inizializzo classe

if($cls->permissionManageObjects()){ # Metodo di controllo per accesso alla pagina di gestione

?>

        <div class="general_incipit">
            <div class="title"> Gestione oggetti </div>
            <div class="subtitle"> Sezione per la gestione degli oggetti</div> <br>


            Da questa pagina e' possibile:
            <ul>
                <li>Creare un oggetto</li>
                <li>Modificare un oggetto</li>
                <li>Eliminare un oggetto</li>
            </ul>

        </div>


    <div class="form_container gestione_oggetti">

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Creazione oggetto</div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <?=$cls->listObjectTypes();?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Indossabile -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Indossabile</div>
                <input type="checkbox" name="indossabile">
            </div>

            <!-- Cariche -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Cariche</div>
                <input type="number" name="cariche">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert_object"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica oggetto</div>


            <!-- LISTA OGGETTI -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Oggetti</div>
                <select name="oggetto">
                    <?=$cls->listObjects();?>
                </select>
            </div>

            <!-- TIPO -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Tipo</div>
                <select name="tipo">
                    <?=$cls->listObjectTypes();?>
                </select>
            </div>

            <!-- NOME -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <!-- Descrizione -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descrizione"></textarea>
            </div>

            <!-- Immagine -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Immagine</div>
                <input type="text" name="immagine">
            </div>

            <!-- Indossabile -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Indossabile</div>
                <input type="checkbox" name="indossabile">
            </div>

            <!-- Cariche -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Cariche</div>
                <input type="number" name="cariche">
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit_object"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina Oggetto</div>

            <!-- LISTA OGGETTI -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Oggetti</div>
                <select name="oggetto">
                    <?=$cls->listObjects();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete_object"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>

    <script src="pages/gestione/oggetti/gestione_oggetti.js"></script>


<?php } ?>
