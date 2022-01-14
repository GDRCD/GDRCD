<?php
if(Permissions::permission('MANAGE_WEATHER') && ($_REQUEST['dir'] )) {
    $class = Meteo::getInstance();


    switch (Filters::out($_POST['op'])) {

        case 'save_chat': // Salvataggio modifiche
            include('meteo/impostazioni/save.inc.php');
            break;

        default: //Lista pagine

            break;
    }

    echo "<form action='popup.php?page=meteo_chat&dir={$_REQUEST['dir']}' method='post'>";
    switch (Functions::get_constant('WEATHER_TYPE')){
        case 1: //stagioni
            ?>
            <div class="single_input">
                <div class="label">Condizione meteo: </div>
                <select name="condizione" id="condizione" >
                    <?php
                    $all= Meteo::getAllCondition();

                    while ($row = DB::query($all, 'fetch')){
                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                    }
                    ?>
                </select>
            </div>
            <?php
            if (Functions::get_constant('WEATHER_WIND'))  {
            ?>
            <div class="single_input">
            <div class="label"><?php echo Filters::out($MESSAGE['interface']['administration']['meteo_condition']['wind_name']); ?></div>
            <select  name="vento[]" id="vento">
                <?php
                echo $class->selectVento();
                ?>
            </select>
            </div>
                <?php }
            ?>
            <div class="single_input">
                <div class="label">Temperatura</div>
                <input type="number" name="temperatura" id="temperatura">
            </div>
            <?php
            break;

        default: //webapi
            ?>
            <div class="single_input">
                <div class="label">Città</div>
                <?php $citta= $class->checkMeteoChat($_REQUEST['dir']);?>
                <input type="text" name="webapi_city" value="<?=$citta['citta']?>">
            </div>

        <?php
            break;
    }
    ?>
    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save_chat">
    </div>

</form>
    <?php
}
