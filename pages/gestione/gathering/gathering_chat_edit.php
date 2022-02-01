<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_chat = GatheringChat::getInstance();
$chat=Chat::getInstance()->getChatData( Filters::out($_GET['id']));

$op = Filters::out($_GET['op']);

?>


<div class="gestione_incipit">
    Modifica Chat "<?=$chat['nome']?>".
</div>
<div class="form_container">

    <?php if(isset($resp)){ ?>
        <div class="warning"><?=$resp['mex'];?></div>
        <div class="link_back"><a href="/main.php?page=gestione_gathering_chat">Indietro</a></div>
        <?php
        Functions::redirect('/main.php?page=gestione_gathering_chat',2);
    } ?>
    <form method="POST" class="form">


        <?php
        if ($gathering_chat->gatheringRarity()) {?>
            <!-- DROPRATE CHAT -->
            <div class="single_input"> <!-- STANDARD INPUT CONTAINER -->
                <div class="label">Drop Rate della Chat</div>
                <input type="number" name="drop_rate" value="<?=$chat['drop_rate']?>">
            </div>
        <?php
        }
        ?>
        <!-- bottoni -->
        <div class="single_input">
            <div class='form_submit'>
                <input type="hidden" name="action" value="edit_chat">
                <input type="hidden" name="id" required value="<?php echo Filters::out($_GET['id']); ?>"/>
                <input type="submit"  value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            </div>
        </div>

    </form>
        <hr>
    <!--qui va la tabella con tutte gli oggetti droppabili in chat-->
    <div class="fake-table gathering_list">

    <?php
   echo ($gathering_chat->GatheringChatItemList(Filters::out($_GET['id'])));
    ?>
    </div>





    <script src="pages/gestione/gathering/JS/gathering_chat_edit.js"></script>
    <script src="pages/gestione/gathering/JS/gathering_chat_delete_item.js"></script>


    <div class="link_back"><a href="/main.php?page=gestione_gathering_chat">Indietro</a></div>
</div>