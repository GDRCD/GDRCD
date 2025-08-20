<?php

/**
 * @class AudioController
 * @note Classe base per la gestione delle notifiche audio in GDRCD
 */
class AudioController
{
    /**
     * @fn isSoundAllowed
     * @note Indica se i suoni sono abilitati in base alle configurazioni della land e del giocatore connesso
     * @return bool
     */
    public static function isSoundAllowed($label)
    {
        return (
            $GLOBALS['PARAMETERS']['mode']['allow_audio'] == 'ON'
            && !empty($GLOBALS['PARAMETERS']['settings']['audio_new_'.$label])
        ) || $_SESSION['blocca_media'] != 1;
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
        if(!self::isSoundAllowed($label)) {
            return NULL;
        }

        // Recupero i parametri
        $audioFile = $GLOBALS['PARAMETERS']['settings']['audio_new_'.$label];
        $ext = explode('.', $audioFile);

        $audioType = $GLOBALS['PARAMETERS']['settings']['audiotype']['.' . strtolower(end($ext))] ?? null;

        if($audioType) {
            $playFunction = self::playFunction($label);
            $stopFunction = self::stopFunction($label);

            return <<<HTML
                <div style="height:0;">

                    <audio id="audioController_{$label}" preload="none" controls style="display:none">
                        <source src="../../../sounds/{$audioFile}" type="{$audioType}">
                        Your browser does not support the audio element.
                    </audio>

                </div>
                <script type="text/javascript">

                    function {$playFunction} {
                        let mediaElementChat = document.getElementById("audioController_{$label}");
                        mediaElementChat.play();
                        console.log('playAudio_{$label}');
                    }

                    function {$stopFunction} {
                        let mediaElementChat = document.getElementById("audioController_{$label}");
                        mediaElementChat.pause();
                        mediaElementChat.currentTime = 0;
                        console.log('stopAudio_{$label}');
                    }

                </script>
                HTML;
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

        // Esco nel caso in cui siano disattivati i suoni
        if(!self::isSoundAllowed($label) || $_SESSION['blocca_media'] == 1 ) {
            return NULL;
        }

        // Prevedo la possibilità di essere dentro un iFrame
        $document = $isChild ? 'parent.document' : 'document';

        return  '<script type="text/javascript">
                let mediaElementChat = '.$document.'.getElementById("audioController_'.$label.'");
                mediaElementChat.play();
            </script>';
    }

    public static function playFunction($label) {
        return 'playAudio_'. $label .'()';
    }

    public static function stopFunction($label) {
        return 'stopAudio_'. $label .'()';
    }
}
