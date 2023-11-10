<?php

Router::loadRequired();

$conversation_id = Filters::int($_GET['conversation']);
$op = Filters::out($_GET['op']);

?>

<div class="general_title">
    Conversazioni
</div>
<div id="conversations_view_container">

    <?php if ( Conversazioni::getInstance()->conversationsEnabled() ) { ?>
        <div class="conversations_box">

            <div class="conversations_box_list">
                <div class="conversations_commands">
                    <div class="command" title="Nuova conversazione">
                        <a href="/main.php?page=conversazioni/index">
                            <i class="fas fa-plus-circle"></i>
                        </a>
                    </div>
                </div>
                <div class="conversations_search_box">
                    <div class="title">Cerca</div>
                    <div class="search_body">
                        <div class="single_input">
                            <input type="text" name="title" placeholder="Titolo"/>
                        </div>
                        <div class="single_input">
                            <select name="member">
                                <?= Personaggio::getInstance()->listPgs(0,'PG'); ?>
                            </select>
                        </div>
                        <div class="single_input submit">
                            <button id="search_conversations">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="conversations_list">
                    <?= Conversazioni::getInstance()->conversationsList(); ?>
                </div>
            </div>

            <div class="conversations_messages">
                <?= Conversazioni::getInstance()->conversation($conversation_id, $op); ?>
            </div>
        </div>

        <script src="<?= Router::getPagesLink('conversazioni/view.js'); ?>"></script>


    <?php } else { ?>
        <div class="warning error">Conversazioni disabilitate</div>
    <?php } ?>


</div>
