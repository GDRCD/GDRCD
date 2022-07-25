<?php
$blocco = gdrcd_query("SELECT pg, master, titolo FROM blocco_esiti WHERE id='" . gdrcd_filter('num', $_GET['blocco']) . "' 
    LIMIT 1 ");

if ( $_GET['op'] == 'new' ) {
    ?>

    <div class="page_title">
        <h2>Serie di esiti: <? echo $blocco['titolo']; ?></h2>
    </div>

    <div class="form_info">
        <? echo $MESSAGE['interface']['esiti']['newesito']; ?>
    </div>
    <form action="main.php?page=gestione_segnalazioni&segn=esito_index"
          method="post"
          class="form_gestione">

        <div class='form_label'>
            Titolo
        </div>
        <div class='form_field'>
            <input name="titolo"
                   value=""/>
        </div>
        <div class='form_label'>
            Contenuto ON
        </div>
        <div class='form_field'>
            <textarea name="contenuto"></textarea>
        </div>

        <div class="form_info">
            Descrivere dettagliatamente quel che il personaggio può conoscere o scoprire, secondo la coerenza del caso,
            in maniera narrativa (come fareste in chat).
        </div>
        <? if (Functions::get_constant('ESITI_TIRI');) { ?>
        <div class='form_label'>
            Tira dei dadi
        </div>
        <div class='form_field'>
            Numero di dadi: <input name="dice_num" value=""/><br>
            Numero di facce dei dadi: <input name="dice_face" value=""/><br>
        </div>
        <? } ?>
        <div class='form_label'>
            Note OFF
        </div>
        <div class='form_field'>
            <input name="note" value=""/>
        </div>
        <div class="form_info">
            Utilizzare solo per brevi chiarimenti
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
            <input type="hidden"
                   name="op"
                   value="add">
            <input type="hidden"
                   name="id"
                   value="<? echo $_GET['blocco']; ?>">
            <input type="submit"
                   value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
        </div>

    </form>
<? }

?>