<?php
    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    // Ottengo il numero di utenti cancellati
    $deletedUsers = gdrcd_query("SELECT COUNT(*) AS deletedUsers FROM personaggio WHERE permessi = -1");
    $deletedUsers = $deletedUsers['deletedUsers'];

    ?>

<!-- Log -->
<div id="ManutenzioneCleanLogs" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_log']['title']); ?></div>
        <!-- Campi -->
        <div class='single_input'>
            <select name="mesi" >
                <?php for($i = 0; $i <= 12; $i++) { ?>
                    <option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['months']); ?></option>
                <?php } ?>
            </select>
            <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_log']['info']); ?></div>
        </div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="old_log">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>

<!-- Chat -->
<div id="ManutenzioneCleanChat" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_chat']['title']); ?></div>
        <!-- Campi -->
        <div class='single_input'>
            <select name="mesi" >
                <?php for($i = 0; $i <= 12; $i++) { ?>
                    <option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['months']); ?></option>
                <?php } ?>
            </select>
            <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_chat']['info']); ?></div>
        </div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="old_chat">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>

<!-- Messaggi -->
<div id="ManutenzioneCleanMessages" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_messages']['title']); ?></div>
        <!-- Campi -->
        <div class='single_input'>
            <select name="mesi" >
                <?php for($i = 0; $i <= 12; $i++) { ?>
                    <option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['months']); ?></option>
                <?php } ?>
            </select>
            <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['old_messages']['info']); ?></div>
        </div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="old_messages">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>

<!-- Utenti Temporaneamente Eliminati -->
<div id="ManutenzioneDeletedUsers" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['deleted_users']['title']); ?></div>
        <!-- Info -->
        <div class='info'>Personaggi coinvolti: <strong><?=$deletedUsers;?></strong></div>
        <div class='info'><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['deleted_users']['info']); ?></div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="deleted_users">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>

<!-- Utenti Non Attivi -->
<div id="ManutenzioneMissingUsers" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['missing_users']['title']); ?></div>
        <!-- Campi -->
        <div class='single_input'>
            <select name="mesi" >
                <?php for($i = 0; $i <= 12; $i++) { ?>
                    <option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['months']); ?></option>
                <?php } ?>
            </select>
            <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['missing_users']['info']); ?></div>
        </div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="missing_users">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>

<!-- Blacklisted -->
<div id="ManutenzioneCleanBlacklist" class="form_container form_container_bg">
    <form action="main.php?page=gestione/manutenzione" method="post" class="form">
        <!-- Titolo -->
        <div class="title"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['blacklisted']['title']); ?></div>
        <!-- Info -->
        <div class='info'><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['blacklisted']['info']); ?></div>
        <!-- Bottoni -->
        <div class='form_submit'>
            <input type="hidden" name="op" value="blacklisted">
            <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
        </div>
    </form>
</div>