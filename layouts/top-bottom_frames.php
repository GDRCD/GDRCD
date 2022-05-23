<?php
/** * Pagina di layout.
 * E' selezionabile come layout principale per il proprio gdr semplicemente da config.inc.php
 * Contiene il css che viene richiamato separatamente come file esterno e il markup
 *
 * Il layout è a piena compatibilità con i browser.
 * La scelta di inserire qui il css ad esso destinato è per limitarne la modifica da parte dell'utente
 * consentendogli di personalizzare tutto il resto senza rovinare la compatibilità cross browser
 *
 * @author Blancks
 */
if (isset($_GET['css']))
{
    header('Content-Type:text/css; charset=utf-8');


    ?>@charset "utf-8";

    body{
    margin: 0;
    padding: 0;
    border: 0;
    overflow: hidden;
    height: 100%;
    max-height: 100%;
    }

    #framecontentTop, #framecontentBottom{
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100px; /*Height of top frame div*/
    overflow: hidden; /*Disable scrollbars. Set to "scroll" to enable*/
    }

    #framecontentBottom{
    top: auto;
    bottom: 0;
    height: 100px; /*Height of bottom frame div*/
    overflow: hidden; /*Disable scrollbars. Set to "scroll" to enable*/
    }

    #maincontent{
    position: fixed;
    top: 100px !important; /*Set top value to HeightOfTopFrameDiv*/
    left: 0;
    right: 0;
    bottom: 100px !important; /*Set bottom value to HeightOfBottomFrameDiv*/
    overflow: auto;
    }

    .innertube{
    margin: 15px; /*Margins for inner DIV inside each DIV (to provide padding)*/
    }

    * html body{ /*IE6 hack*/
    padding: 100px 0 100px 0; /*Set value to (HeightOfTopFrameDiv 0 HeightOfBottomFrameDiv 0)*/
    }

    * html #maincontent{ /*IE6 hack*/
    height: 100%;
    width: 100%;
    }


    <?php

} else
{


    if ($PARAMETERS['bottom_column']['activate'] == 'ON')
    {

        ?>
        <!-- Riga inferiore -->
        <div id="framecontentBottom">
            <div class="innertube">

                <div class="colonne_bot">
                    <?php
                    foreach ($PARAMETERS['bottom_column']['box'] as $box)
                    {
                        echo '<div class="' . $box['class'] . '">';

                        gdrcd_load_modules($box['page'], $box);

                        echo '</div>';
                    }

                    ?>
                </div>

            </div>
        </div>
        <?php

    }


    if ($PARAMETERS['top_column']['activate'] == 'ON')
    {
        ?>

        <!-- Riga superiore  -->
        <div id="framecontentTop">
            <div class="innertube">

                <div class="colonne_top">
                    <?php

                    foreach ($PARAMETERS['top_column']['box'] as $box)
                    {
                        echo '<div class="' . $box['class'] . '">';

                        gdrcd_load_modules($box['page'], $box);

                        echo '</div>';

                    }

                    ?>
                </div>

            </div>
        </div>

        <?php

    }
    ?>

    <div id="maincontent">
        <div class="output">
            <?php gdrcd_load_modules($strInnerPage); ?>
        </div>
    </div>

    <?php

}

?>