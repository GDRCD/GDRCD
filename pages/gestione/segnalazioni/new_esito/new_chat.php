<?php
$blocco = gdrcd_query("SELECT pg, master, titolo FROM blocco_esiti WHERE id='".gdrcd_filter('num',$_GET['blocco'])."' 
    LIMIT 1 ");
if ($_SESSION['permessi']>=ESITI_PERM && ESITI_CHAT && $blocco['pg']!==$_SESSION['login']){
    if ($_GET['op']=='newchat') { ?>

        <div class="page_title">
            <h2>Esito in chat</h2>
        </div>
        <div class="form_info">
            <? echo $MESSAGE['interface']['esiti']['esitochat'];?>
        </div>

        <form action="main.php?page=gestione_segnalazioni&segn=esito_index"
              method="post"
              class="form_gestione">

            <div class='form_label'>
                Titolo
            </div>
            <div class='form_field'>
                <input name="titolo" value=""/>
            </div>

            <div class='form_label'>
                Scegli la chat
            </div>
            <div class='form_field'>
                <? $quer=gdrcd_query("SELECT * FROM mappa ORDER BY id ", 'result'); ?>
                <select name="chat">
                    <? while ($res=gdrcd_query($quer, 'fetch')) { ?>
                        <option value="<? echo $res['id'];?>"><? echo $res['nome'];?></option>
                    <? } ?>
                </select>
            </div>

            <div class='form_label'>
                Skill da tirare
            </div>
            <div class='form_field'>
                <? $ability=gdrcd_query("SELECT * FROM abilita WHERE id_razza = -1 ORDER BY nome ", 'result'); ?>
                <select name="id_ab">
                    <? while ($r_ab=gdrcd_query($ability, 'fetch')) { ?>
                        <option value="<? echo $r_ab['id_abilita'];?>"><? echo $r_ab['nome'];?></option>
                    <? } ?>
                </select>
            </div>

            <div class='form_label'>
                Modificatore
            </div>
            <div class="form_field">
                <input name="mod" value=""/>
            </div>

            <div class='form_label'>
                Esito dato dal superamento della prima CD
            </div>
            <div class='form_field'>
                <textarea name="CD_1"></textarea>
            </div>

            <div class='form_label'>
                Esito dato dal superamento della seconda CD
            </div>
            <div class='form_field'>
                <textarea name="CD_2"></textarea>
            </div>

            <div class='form_label'>
                Esito dato dal superamento della terza CD
            </div>
            <div class='form_field'>
                <textarea name="CD_3"></textarea>
            </div>

            <div class='form_label'>
                Esito dato dal superamento della quarta CD
            </div>
            <div class='form_field'>
                <textarea name="CD_4"></textarea>
            </div>

            <!-- bottoni -->
            <div class='form_submit'>
                <input type="hidden"
                       name="op"
                       value="add">
                <input type="hidden"
                       name="id"
                       value="<? echo $_GET['blocco'];?>">
                <input type="submit"
                       value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
            </div>

        </form>
    <? }
}
?>