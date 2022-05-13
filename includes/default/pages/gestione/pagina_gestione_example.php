<?php

require_once(__DIR__ . '/../../core/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Example::getInstance(); # Inizializzo classe

if($cls->managePermission()){ # Metodo di controllo per accesso alla pagina di gestione

?>
        <div class="general_incipit">
            <div class="title"> Un titolo </div>
            <div class="subtitle">Un sottotitolo</div>

            Testo testo <span class="highlight"> Un testo che si nota nel testo </span> test test

            <ul>
                <li>Elemento 1</li>
                <li>Elemento 2</li>
                <li>Elemento 3</li>
                <li>Elemento 4</li>
            </ul>

        </div>

    <div class="form_container">

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_insert"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_edit"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title"><!-- TITOLO SEZIONE --></div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label"></div>
                <!-- INPUT OR SELECT OR TEXTAREA -->
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                <input type="hidden" name="example" value="ex_value"> <!-- EXAMPLE OF OTHER HIDDEN DATA -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>


<?php } ?>
