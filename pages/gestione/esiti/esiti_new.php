<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
$abi = Abilita::getInstance();
$chat = new Chat();
$id_record = Filters::int($_GET['id_record']);

$op = Filters::out($_POST['op']);

switch ($op) {
    case 'new':
        $resp = $esiti->newEsitoManagement($_POST);
        break;
}

?>

<div class="gestione_incipit">
    Creazione nuova conversazione esito.
</div>

<div class="form_container">

    <?php if(isset($resp)){ ?>
        <div class="warning"><?=$resp['mex'];?></div>
        <div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>
    <?php
        Functions::redirect('/main.php?page=gestione_esiti',2);
    } ?>

    <form method="POST" class="form">


        <div class="single_input">
            <div class='label'>
                Titolo
            </div>
            <input name="titolo" required/>
        </div>

        <div class="single_input">
            <div class='label'>
                Masterscreen
            </div>
            <textarea name="contenuto" required></textarea>
        </div>

        <?php if ($esiti->esitiTiriEnabled()) { ?>
            <div class="single_input">
                <div class='label'>
                    Numero di dadi
                </div>
                <input name="dice_num"/><br>
            </div>

            <div class="single_input">
                <div class='label'>
                    Numero di facce dei dadi
                </div>
                <input name="dice_face"/>
            </div>
            <div class="single_input">
                <div class="label">Abilità</div>
                <select name="abilita">
                    <?=$abi->ListaAbilita();?>
                </select>
            </div>
            <div class="single_input">
                <div class="label">Chat</div>
                <select name="chat">
                    <?=$chat->chatList();?>
                </select>
            </div>

            <div class="cd_box">

            </div>

            <div id="cd_add"><button type="button">Aggiungi cd</button> </div>
        <?php } ?>

        <div class="form_info">
            Utilizzato solo per brevi chiarimenti
        </div>

        <!-- bottoni -->
        <div class="single_input">
            <div class='form_submit'>
                <input type="hidden" name="op" value="new">
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>

    <script src="pages/gestione/esiti/JS/esiti_new.js"></script>

    <div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>
</div>
