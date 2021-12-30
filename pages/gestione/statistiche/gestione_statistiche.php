<?php

require_once(__DIR__.'/../../../includes/required.php'); # Inserisco il required se non presente, per futuro spostamento in modale/ajax

$cls = Statistiche::getInstance(); # Inizializzo classe

if($cls->permissionManageStatistics()){ # Metodo di controllo per accesso alla pagina di gestione


    if(isset($_POST['op'])){ # Se ho richiesto un'operazione
        switch ($_POST['op']){ # In base al tipo di operazione eseguo insert/edit/delete/altro
            case 'op_insert':
                $resp = $cls->insertStat($_POST);
                break;
            case 'op_edit':
                $resp = $cls->editStat($_POST);
                break;
            case 'op_delete':
                $resp = $cls->deleteStat($_POST);
                break;
        }
    }


?>

        <div class="general_incipit">
            <div class="title"> Gestione statistiche </div>
            <div class="subtitle">Pagina di gestione delle statistiche personaggio</div>
            <br><br>
            Per ogni statistica è possibile impostare:<br>
            <ul>
                <li>Nome</li>
                <li>Descrizione</li>
                <li>Valore massimo - Il valore massimo raggiungibile per quella <span class="highlight">SINGOLA</span> statistica</li>
                <li>Valore minimo - Valore minimo necessario in iscrizione per quella <span class="highlight">SINGOLA</span> statistica</li>
            </ul>
            <br><br>
            In questa pagina è possibile:
            <br>
            <ul>
                <li>Creare una statistica</li>
                <li>Modificare una statistica</li>
                <li>Eliminare una statistica</li>
            </ul>
            <br>
            <br>
            <div class="highlight">ELIMINARE UNA STASTICA NON CAMBIA I PUNTI IN CUI QUESTA E' ASSOCIATA E RICHIEDE UN INTERVENTO MANUALE.</div>
        </div>


    <div class="form_container">

        <?php if(isset($resp)) { # Se ho inviato il form e ricevuto una risposta ?>

            <div class="warning"><?=$resp['mex'];?></div>
            <a href="/main.php?page=gestione_statistiche">Indietro</a>

        <?php
            Functions::redirect('/main.php?page=gestione_statistiche',3); # Redirect alla stessa pagina, per evitare il re-submit di un form
        } ?>

        <!-- INSERT -->
        <form method="POST" class="form">

            <div class="form_title">Crea Statistica</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descr"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore Massimo</div>
                <input type="number" name="max_val">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore Minimo</div>
                <input type="number" name="min_val">
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_insert"> <!-- OP NEEDED -->
                <input type="submit" value="Crea">
            </div>

        </form>

        <!-- EDIT -->
        <form method="POST" class="form edit-form">

            <div class="form_title">Modifica Statistica</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Statistica</div>
                <select name="stat">
                    <option value=""></option>
                    <?=$cls->listStats();?>
                </select>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Nome</div>
                <input type="text" name="nome">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Descrizione</div>
                <textarea name="descr"></textarea>
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore Massimo</div>
                <input type="number" name="max_val">
            </div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Valore Minimo</div>
                <input type="number" name="min_val">
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_edit"> <!-- OP NEEDED -->
                <input type="submit" value="Modifica">
            </div>

        </form>

        <!-- DELETE -->
        <form method="POST" class="form">

            <div class="form_title">Elimina Statistica</div>

            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Statistica</div>
                <select name="stat">
                    <option value=""></option>
                    <?=$cls->listStats();?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="op_delete"> <!-- OP NEEDED -->
                <input type="submit" value="Elimina">
            </div>

        </form>

    </div>

    <script src="/pages/gestione/statistiche/gestione_statistiche.js"></script>


<?php } ?>
