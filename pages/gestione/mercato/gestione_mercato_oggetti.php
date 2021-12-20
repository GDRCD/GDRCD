<?php

require_once(__DIR__ . '/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Mercato::getInstance(); # Inizializzo classe

if ($cls->manageShopObjectsPermission()) { # Metodo di controllo per accesso alla pagina di gestione

    $obj_class = Oggetti::getInstance();

    if (isset($_POST['op'])) { # Se ho richiesto un'operazione
        switch ($_POST['op']) { # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertShopObj($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editShopObj($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteShopObj($_POST);
                break;
        }
    }


    ?>

    <div class="general_incipit">
        <div class="title"> Gestione Oggetti Mercato</div>
        <div class="subtitle">Gestione degli oggetti presenti nel mercato</div>

        <div class="highlight"> Pagina di gestione degli oggetti del mercato. </div><br>

        La pagina gestisce solo gli oggetti assegnati al mercato. Per gestire gli oggetti,
        serve modificarli dalla pagina Gestione Oggetti.
        <br><br>

        Da questa pagina è possibile:
        <ul>
            <li>Assegnare un oggetto ad un negozio, con relativa quantita' e costo</li>
            <li>Modificare la quantita' ed il costo di un oggetto assegnato ad un negozio</li>
            <li>Rimuovere un oggetto da un negozio</li>
        </ul>

    </div>


    <div class="form_container">

        <?php if (isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?= $resp['mex']; ?></div>
            <div class="link_back">
                <a href="/main.php?page=gestione_mercato_oggetti">
                    Indietro
                </a>
            </div>

            <?php
            Functions::redirect('/main.php?page=gestione_mercato_oggetti', 3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Aggiunta oggetti a negozio</div>

            <div class="single_input">
                <div class="label">Negozio</div>
                <select name="negozio" required>
                    <option value=""></option>
                    <?= $cls->listShops(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <option value=""></option>
                    <?= $obj_class->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Quantità</div>
                <input type="number" name="quantity" required>
            </div>

            <div class="single_input">
                <div class="label">Costo</div>
                <input type="number" name="costo" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form">

            <div class="form_title">Modifica oggetti negozio</div>

            <div class="single_input">
                <div class="label">Negozio</div>
                <select name="negozio" required>
                    <option value=""></option>
                    <?= $cls->listShops(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <option value=""></option>
                    <?= $obj_class->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Quantità</div>
                <input type="number" name="quantity" required>
            </div>

            <div class="single_input">
                <div class="label">Costo</div>
                <input type="number" name="costo" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina oggetto da negozio</div>

            <div class="single_input">
                <div class="label">Negozio</div>
                <select name="negozio" required>
                    <option value=""></option>
                    <?= $cls->listShops(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Oggetto</div>
                <select name="oggetto" required>
                    <option value=""></option>
                    <?= $obj_class->listObjects(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Invia">
            </div>

        </form>

    </div>


<?php } ?>