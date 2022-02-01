<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_chat = GatheringChat::getInstance();
$gathering_cat = GatheringCategory::getInstance();
$gathering_item = GatheringItem::getInstance();

$chat = Chat::getInstance();


$op = Filters::out($_GET['op']);
?>


<div class="gestione_incipit">
    Creazione Nuova Combinazione
</div>
<div class="form_container">

    <?php if(isset($resp)){ ?>
        <div class="warning"><?=$resp['mex'];?></div>
        <div class="link_back"><a href="/main.php?page=gestione_gathering_item">Indietro</a></div>
        <?php
        Functions::redirect('/main.php?page=gestione_gathering_item',2);
    } ?>
    <form method="POST" class="form form-chat">

        <div class="single_input">
            <div class="label">Lista Chat</div>
            <select name="chat">
                <?=$chat->chatList();?>
            </select>
        </div>
        <?php
            if ($gathering_chat->gatheringRarity()) {?>
        <!-- DROPRATE CHAT -->
        <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
            <div class="label">Drop Rate della Chat</div>
            <input type="number" name="drop_rate">
        </div>
        <?php
        }
            ?>
        <hr>

        <div class="single_input">
            <div class="label">Oggetto</div>
            <select name="item">
                <?=$gathering_item->listGatheringItem();?>
            </select>
        </div>
        <?php
        if ($gathering_chat->gatheringRarity()) {?>
        <div class="single_input">
            <div class="label">Percentuale di Drop</div>
            <input type="number" name="percentuale">
        </div>
        <div class="single_input">
            <div class="label">Quantità minima</div>
            <input type="number" name="quantita_min">
        </div>
        <div class="single_input">
            <div class="label">Quantità massima</div>
            <input type="number" name="quantita_max">
        </div>
            <?php
        }
        ?>
        <?php
        if ($gathering_chat->gatheringAbility()) {?>
            <!-- DROPRATE CHAT -->
            <div class="single_input">
                <div class="label">Livello minimo Abilità</div>
                <input type="number" name="livello_abi">
            </div>
            <?php
        }
        ?>

        <!-- bottoni -->
        <div class="single_input">
            <div class='form_submit'>
                <input type="hidden" name="action" value="new_chat">
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>
    <script src="pages/gestione/gathering/JS/gathering_chat.js"></script>

    <div class="link_back"><a href="/main.php?page=gestione_gathering_chat">Indietro</a></div>
</div>