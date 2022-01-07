<?php
$class =Meteo::getInstance();

$op = Filters::out($_REQUEST['op']);

?>
<!-- Titolo della pagina -->
<div class="page_title">
    <h2>Impostazioni generali Meteo</h2>
</div>
<?php

switch (Filters::out($_POST['op'])) {
    case 'save': // Salvataggio modifiche
        include('meteo/impostazioni/save.inc.php');
        break;

    default: //Lista pagine



        break;
}
/*
 * In questa pagina si potranno inserire le impostazioni principali legate al meteo in modo da poterle disabilitare/abilitare.
 * Le impostazioni saranno su:
 * - tipo di meteo (stagioni / webapi)
 * - luna si/no
 * - vento si/no
 *
 */
?>
<form class="form" action="main.php?page=gestione_meteo_impostazioni" method="post"><!-- Type -->
<div class="single_input">
    <div class="label">Vento</div>
    <select name="wind" required>
        <option value="1"<?php echo (Functions::get_constant('WEATHER_WIND')==1)  ? "selected" : 0; ?> >Abilitato</option>
        <option value="0" <?php echo (Functions::get_constant('WEATHER_WIND')==0)  ? "selected" : 0; ?>>Disabilitato</option>

    </select>
</div>

<!-- MOON -->
<div class="single_input">
    <div class="label">Fasi Lunari</div>
    <select name="moon" required>
        <option value="1"<?php echo (Functions::get_constant('WEATHER_MOON')==1)  ? "selected" : 0; ?> >Abilitato</option>
        <option value="0" <?php echo (Functions::get_constant('WEATHER_MOON')==0)  ? "selected" : 0; ?>>Disabilitato</option>
    </select>
</div>


<!--Type -->
<div class="single_input">
    <div class="label">Tipo di meteo</div>
    <select name="type" required>
        <option value="1"<?php echo (Functions::get_constant('WEATHER_TYPE')==1)  ? "selected" : 0; ?> >Stagioni</option>
        <option value="0" <?php echo (Functions::get_constant('WEATHER_TYPE')==0)  ? "selected" : 0; ?>>Web Api</option>
    </select>
</div>



<?php if (Functions::get_constant('WEATHER_TYPE')==0){?>
        <h2>Impostazioni webApi</h2>
    OpenWeatherMap è un servizio gratuito che offre una API dedicata al servizio di dati sulle previsioni meteo.
    <strong>Come ottenere la chiave API per dati meteo:</strong>
    Per motivi di sicurezza OpenWeatherMap, come molte altre API richiede una registrazione della propria App, per ottenere una API Key.
    Per registrare la nostra App navighiamo all'indirizzo http://openweathermap.org/appid  e selezioniamo "Sign up".
    Dovrete quindi creare un account, e successivamente recarvi sul vostro profilo->My Api Keys. Qui troverete una chiave webApi da incollare nel form sottostante insieme al nome della città della quale intendete impostare il meteo.

    <div class="single_input">
        <div class="label">Webapi Key</div>
        <input type="text" name="webapi_key" value="<?php echo Functions::get_constant('WEATHER_WEBAPI')?>">
    </div>
    <div class="single_input">
        <div class="label">Città</div>
        <input type="text" name="webapi_city" value="<?php echo Functions::get_constant('WEATHER_WEBAPI_CITY')?>">
    </div>
 <h2>Impostazioni icone webApi</h2>
    <div class="single_input">
        <div class="label">Icone</div>
        <select name="webapi_icon" required>
            <option value="0"<?php echo (Functions::get_constant('WEATHER_WEBAPI_ICON')==0)  ? "selected" : 0; ?> >Default</option>
            <option value="1" <?php echo (Functions::get_constant('WEATHER_WEBAPI_ICON')==1)  ? "selected" : 0; ?>>Personalizzate</option>
        </select>
    </div>
    <?php if (Functions::get_constant('WEATHER_WEBAPI_ICON')==1){?>
    <div class="single_input">
        <div class="label">Formato immagini</div>
        <select name="webapi_format" required>
            <option value="png"<?php echo (Functions::get_constant('WEATHER_WEBAPI_FORMAT')=='png')  ? "selected" : 0; ?> >PNG</option>
            <option value="gif" <?php echo (Functions::get_constant('WEATHER_WEBAPI_FORMAT')=='gif')  ? "selected" : 0; ?>>GIF</option>
            <option value="jpg" <?php echo (Functions::get_constant('WEATHER_WEBAPI_FORMAT')=='jpg')  ? "selected" : 0; ?>>JPG</option>
        </select>
    </div>
    <?php
        $estensione=Functions::get_constant('WEATHER_WEBAPI_FORMAT');
    }else {
        $estensione='png';
    }
   $url= (Functions::get_constant('WEATHER_WEBAPI_ICON')==0)  ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";


} ?>
    <div class="single_input">
        <input type="submit" name="submit" value="Salva"/>
        <input type="hidden" name="op" value="save">
    </div>

</form>
    <a href="main.php?page=gestione" class="btn-link" >Torna indietro</a>
<?php if (Functions::get_constant('WEATHER_TYPE')==0){?>
<hr>

        <p>Di seguito troverete una tabella con i nomi delle immagini corrispondenti al tipo di condizione meteo e la relativa anteprima dell'immagine.
            <br>Se utilizzate la versione personalizzata di icone, dovrete salvare le immagini con i nomi indicati qui di seguito e salvarle all'interno della cartella "imgs/meteo/" </p>
    <table>

        <thead><tr><th>Icona diurna</th><th>Icona notturna</th><th>Descrizione</th> </tr></thead>
        <tbody>
        <tr><td>01d.<?=$estensione?> <img src=<?php echo $url."01d.{$estensione}";?>></td><td>01n.png <img src=<?php echo $url."01n.{$estensione}";?>></td><td>Sereno</td></tr>
        <tr><td>02d.png <img src=<?php echo $url."02d.{$estensione}";?>></td><td>02n.png <img src=<?php echo $url."02n.{$estensione}";?>></td><td>Poco nuvoloso</td></tr>
        <tr><td>03d.png <img src=<?php echo $url."03d.{$estensione}";?>></td><td>03n.png <img src=<?php echo $url."03n.{$estensione}";?>></td><td>Nubi sparse</td></tr>
        <tr><td>04d.png <img src=<?php echo $url."04d.{$estensione}";?>></td><td>04n.png <img src=<?php echo $url."04n.{$estensione}";?>></td><td>Nuvoloso</td></tr>
        <tr><td>09d.png <img src=<?php echo $url."09d.{$estensione}";?>></td><td>09n.png <img src=<?php echo $url."09n.{$estensione}";?>></td><td>Pioggerella</td></tr>
        <tr><td>10d.png <img src=<?php echo $url."10d.{$estensione}";?>></td><td>10n.png <img src=<?php echo $url."10n.{$estensione}";?>></td><td>Pioggia</td></tr>
        <tr><td>11d.png <img src=<?php echo $url."11d.{$estensione}";?>></td><td>11n.png <img src=<?php echo $url."11n.{$estensione}";?>></td><td>Temporale</td></tr>
        <tr><td>13d.png <img src=<?php echo $url."13d.{$estensione}";?>></td><td>13n.png <img src=<?php echo $url."13n.{$estensione}";?>></td><td>Neve</td></tr>
        <tr><td>50d.png <img src=<?php echo $url."50d.{$estensione}";?>></td><td>50n.png <img src=<?php echo $url."50n.{$estensione}";?>></td><td>Nebbia</td></tr>



        </tbody>
    </table>


<?php
}
?>