<?php
$info = gdrcd_query("SELECT nome, stanza_apparente, invitati, privata, proprietario, scadenza FROM mappa WHERE id=".$_SESSION['luogo']." LIMIT 1");

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="generator" content="AlterVista - Editor HTML"/>
  <link rel="stylesheet" href="/themes/advanced/main.css" type="text/css">
  <title>Blocco Notes Dusk</title>
    <style>

        .blocconote {

            margin: auto;
            padding: 15px;
            text-align: center;
        }
        .blocconote * {
            margin: 5px 0px;
            text-align: justify;
        }

    </style>

</head>
<body>
    <div class="blocconote">
        <div style=" width: 20%;  display: inline-grid">
            <label style="display: flex;    text-align: center;">Tipo:</label>
            <select name="type" id="type">
                <option value="0"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][0]);//parlato?></option>
                <option value="1"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][1]);//azione?></option>
                <option value="4"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][4]);//sussurro?></option>
                <?php if($_SESSION['permessi'] >= GAMEMASTER) { ?>
                    <option value="2"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][2]);//master?></option>
                    <option value="3"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][3]);//png?></option>
                <?php } ?>
                <?php if(($info['privata'] == 1) && (($info['proprietario'] == $_SESSION['login']) || ((is_numeric($info['proprietario']) === true) && (strpos($_SESSION['gilda'], ''.$info['proprietario']))))) { ?>
                    <option value="5"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][5]);//invita?></option>
                    <option value="6"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][6]);//caccia?></option>
                    <option value="7"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][7]);//elenco?></option>
                <?php }//if
                ?>
            </select>
        </div>
        <div style=" width: 20%; display: inline-grid">
            <label style="display: flex;    text-align: center;">Tag:</label>
            <input type="text" id="tag" style=" width: 100%; "  onKeyUp ="blocco_note_function()" placeholder="Tag" >
        </div>
        <div style=" width:100%; display: inline-grid; margin: 0 auto">
            <textarea id="testo" onKeyUp="blocco_note_function(), conta()" placeholder="Testo Azione" style="width: 100%"></textarea>
        </div>
            <span id="conta">Caratteri: </span><br>
            <button onClick="manda()" style="width: 100px; ">Manda</button>

    </div>

<script>
function blocco_note_function() {
    window.opener.document.getElementById('tipo').value = document.getElementById('tipo').value;
	window.opener.document.getElementById('tag').value = document.getElementById('tag').value;
	window.opener.document.getElementById('testo').value = document.getElementById('testo').value;
}
function manda() {
	window.opener.document.getElementById('inviaAzione').click();
    $("#testo").val("");
}
function conta() {
	document.getElementById("conta").innerHTML = 'Caratteri: '+document.getElementById("testo").value.length;
}
</script>

</body>
</html>