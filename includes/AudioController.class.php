<?php

/**
 * @class AudioController
 * @note Classe base per la gestione delle notifiche audio in GDRCD
 */
class AudioController
{
    /**
     * @fn isSoundAllowed
     * @note Controllo sulla abilitazione dei suoni nelle configurazioni
     * @return bool
     */
    public static function isSoundAllowed($label) {
        global $PARAMETERS;
        return $PARAMETERS['mode']['allow_audio'] == 'ON' && !empty($PARAMETERS['settings']['audio_new_'.$label]);
    }

    /**
     * @fn build
     * @note Costruisco il controllore dei suoni
     * @param string $label Identificativo del componente audioControllaer
     * @return string
     */
    public static function build($label = NULL)
    {
        // Esco nel caso non sia stata impostata la tipologia
        if(empty($label)) return NULL;

        // Esco nel caso in cui siano disattivati i suoni
        if(!self::isSoundAllowed($label) && $_SESSION['blocca_media'] == 1 ) {
            return NULL;
        }

        // Recupero i parametri
        global $PARAMETERS;

        // Costruisco il controllore
        $ext = explode('.', $PARAMETERS['settings']['audio_new_'.$label]);
        if(isset($PARAMETERS['settings']['audiotype']['.'.strtolower(end($ext))])) {
            return
                '<div style="height:0;">
                    <audio id="audioController_'.$label.'" preload="none" controls style="display:none">
                        <source src="../../../sounds/'.$PARAMETERS['settings']['audio_new_'.$label].'" type="'.$PARAMETERS['settings']['audiotype']['.'.strtolower(end($ext))].'">
                        Your browser does not support the audio element.
                    </audio>
                </div>';
        }

        return NULL;
    }

    /**
     * @fn play
     * @note Gestisco l'esecuzione o meno dell'audio di notifica
     * @param string $label Identificativo del componente audioControllaer
     * @return string
     */
    public static function play($label = NULL, $isChild = FALSE)
    {
        // Se non è stato dichiarato un tipo, non emetto suono
        if(empty($label)) return NULL;

        // Nel caso in cui siano disattivati i suoni, forzo il loro spegnimento
        if(!self::isSoundAllowed($label) && $_SESSION['blocca_media'] == 1 ) {
            return NULL;
        }

        // Prevedo la possibilità di essere dentro un iFrame
        $document = $isChild ? 'parent.document' : 'document';

        return  '<script type="text/javascript">
                let mediaElementChat = '.$document.'.getElementById("audioController_'.$label.'");
                mediaElementChat.play();
            </script>';
    }
}