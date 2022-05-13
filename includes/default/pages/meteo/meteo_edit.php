<?php

$chat = Personaggio::getPgLocation();
$meteo = Meteo::getInstance();
$chat_data = $meteo->getMeteoChat($chat);

$meteo = Filters::in($chat_data['meteo']);
$vento = Filters::in($chat_data['vento']);
$img = Filters::in($chat_data['img']);
$temp = Filters::in($chat_data['temp']);

?>

<form class="form" method="post" id="form_meteo_edit">
    <div class="single_input">
        <div class="label">Condizione</div>
        <select name="condizione" id="condizione" >
            <option value=""></option>
            <?= MeteoCondizioni::getInstance()->listConditionsByText($meteo) ?>
        </select>
    </div>
    <div class="single_input">
        <div class="label">Vento</div>
        <select name="vento" id="vento" >
            <option value=""></option>
            <?= MeteoVenti::getInstance()->listWindsByText($vento) ?>
        </select>
    </div>
    <div class="single_input">
        <div class="label">Temperatura</div>
        <input type="number" name="temp" id="temp" class="form_input" value="<?=$temp;?>">
    </div>
    <div class="single_input">
        <div class="label">Immagine</div>
        <input type="text" name="img" id="img" class="form_input" value="<?=$img;?>">
    </div>
    <div class="single_input">
        <input type="submit" name="submit" value="Modifica"/>
        <input type="hidden" name="chat" value="<?=$chat;?>"/>
        <input type="hidden" name="action" value="op_edit_chat">
    </div>
</form>

<script src="/includes/default/pagesdes/default/pages/meteo/meteo_edit.js"></script>