<?php

Router::loadRequired();

$conversation_id = Filters::int($_GET['conversation']);
$op = Filters::out($_GET['op']);

?>

<div class="general_title">
    Conversazioni
</div>
<div id="conversations_view_container">

    <?php if ( Conversazioni::getInstance()->isActive() ) { ?>
        <div class="conversations_header">

        </div>
        <div class="conversations_box">

            <div class="conversations_box_list">
                <div class="conversations_commands">
                    <div class="command" title="Nuova conversazione">
                        <a href="/main.php?page=conversazioni/index">
                            <i class="fas fa-plus-circle"></i>
                        </a>
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
