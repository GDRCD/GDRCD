<?php ############################## File Description #######################################

/**        Board Bullettin Decoder.
 *    Il file contiene una funzione di implementazione per il BBCode; pone al primo posto
 *    sicurezza e flessibilità di formattazione mediante le BBTags.
 *
 *    - Il file è salvato in codifica UTF-8 con tabulazione a 4 spazi e breakline unix.
 *
 * @author Salvatore Rotondo <s.rotondo90@gmail.com>
 * @version 2.5
 * @package GDRCD 5.1
 */


##################################### File Functions ########################################

/**    Funzione di formattazione di tutte le bbtags.
 *    Il parametro opzionale va settato su "true" se la stringa che si sta passando alla
 *    funzione ha già subito un escape con htmlentities() o htmlspecialchars().
 *
 *    Features:
 *    - solidi controlli di sicurezza tramite regular expressions ad hoc;
 *    - se la corrispondenza non viene trovata, il tag non viene formattato;
 *    - se lo stesso tag include se stesso, questi non viene formattato;
 *    - include la formattazione basilare del testo;
 *    - include la formattazione parziale di codice html;
 *    - include la formattazione delle immagini.
 *
 *    TagList:
 *    - "[color=X]~[/color]"     formatta il testo col colore specificato, X può essere uguale a hex a 3/6 cifre o testuale (#333, #333333, grey);
 *    - "[bg=X]~[/bg]"         formatta il colore in sottofondo al testo, X vale come per [color];
 *    - "[font=X]~[/font]"     formatta il testo col font specificato, X può essere il nome di un font o più font separati da virgola;
 *    - "[size=X]~[/size]"     formatta il testo alle dimensioni specificate, X è il valore in px;
 *    - "[url]~[/url]"         formatta un link ipertestuale;
 *    - "[mail]~[/mail]"         formatta un link ipertestuale col protocollo 'mailto';
 *    - "[youtube]~[/youtube]" formatta l'oggetto multimediale per visualizzare il video, va inserito il link della pagina del video in questione;
 *
 *
 * @param string $str
 * @param boolean $escaped
 *
 * @return string
 */
function bbdecoder($str, $escaped = false)
{
    global $PARAMETERS;


    $regexpMail = "([a-z0-9._-]+)@[a-z0-9._-]+\.[a-z]{2,4}";
    $regexpUrl = "https?://([-\w\.]+)+(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)?";
    $regexpYT = "https?://www\.youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})(&.*)?";
    $regexpColor = "(\#[0-9a-fA-F]{3,6})|([a-zA-Z]{3,6})";


    $bbcode = [

        "#\[color=($regexpColor)\](.*?(?!\[color).*?)\[/color\]#is" => "<span style=\"color:\\1\">\\4</span>",
        "#\[bg=($regexpColor)\](.*?(?!\[bg).*?)\[/bg\]#is"          => "<span style=\"background-color:\\1\">\\4</span>",
        "#\[font=([a-z A-Z,]+)\](.*?(?!\[font).*?)\[/font\]#is"     => "<span style=\"font-family:\\1\">\\2</span>",
        "#\[size=([0-9]{1,2})\](.*?(?!\[size).*?)\[/size\]#is"      => "<span style=\"font-size:\\1px\">\\2</span>",
        "#\[url\]($regexpUrl)+\[/url\]#is"                          => "<a href=\"\\1\" target=\"_blank\" title=\"\\1\">\\1</a>",
        "#\[mail\]($regexpMail)+\[/mail\]#is"                       => "<a href=\"mailto:\\1\" title=\"\\1\">\\2</a>"

    ];

    if ($PARAMETERS['settings']['bbd']['youtube'] == 'ON')
    {
        $bbcode["#\[youtube\]($regexpYT)+\[/youtube\]#is"] = "<object data=\"http://www.youtube.com/v/\\2&hl=it&fs=1\" type=\"application/x-shockwave-flash\" width=\"425\" height=\"344\"><embed src=\"http://www.youtube.com/v/\\2&hl=it&fs=1\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" width=\"425\" height=\"344\" /></object>";

    }


    if ( ! $escaped)
    {
        $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
    }
    $str = preg_replace(array_keys($bbcode), array_values($bbcode), $str);
    $str = baseformat($str);
    $str = img($str);
    unset($bbcode);

    return $str;
}


/**    Funzione di formattazione basilare del testo.
 *    Se la si vuol applicare singolarmente bisogna passare la stringa prima ad un htmlentities.
 *
 *    Features:
 *    - i tag vuoti non vengono formattati;
 *    - i tag non chiusi non vengono formattati.
 *
 *    TagList:
 *    - "\n"                    breakline html automatica (unix e windows);
 *    - "[hr]"                riga separatrice del testo;
 *    - "[b]~[/b]"            testo in grassetto
 *    - "[i]~[/i]"            testo in corsivo
 *    - "[u]~[/u]"            testo sottolineato
 *    - "[s]~[/s]"            testo barrato
 *    - "[center]~[/center]"    paragrafo centrato
 *    - "[left]~[/left]"        paragrafo a sinistra
 *    - "[right]~[/right]"    paragrafo a destra
 *
 *
 * @param string $str
 *
 * @return string
 */
function baseformat($str)
{
    $bbtag = [
        [
            "\n",
            '[hr]',
            '[b]~[/b]',
            '[i]~[/i]',
            '[u]~[/u]',
            '[s]~[/s]',
            '[center]~[/center]',
            '[right]~[/right]',
            '[left]~[/left]',
            '[quote]~[/quote]'
        ],
        [
            '<br />',
            '<hr />',
            '<strong>~</strong>',
            '<em>~</em>',
            '<ins>~</ins>',
            '<del>~</del>',
            '<p align="center">~</p>',
            '<p align="right">~</p>',
            '<p align="left">~</p>',
            '<span class="quote">~</span>'
        ]
    ];


    $str = " " . $str;
    $par = ['[' => '&#91;', ']' => '&#93;'];

    foreach ($bbtag[0] as $key => $bbt)
    {
        $bbt = explode('~', $bbt);
        $html = explode('~', $bbtag[1][$key]);
        $tagcount = count($bbt);

        while ($posStart = strpos($str, $bbt[0]))
        {
            if ($tagcount == 2)
            {
                if ($posEnd = strpos(substr($str, $posStart), $bbt[1]))
                {
                    $taglen = strlen($bbt[0]);
                    $source = substr($str, $posStart + $taglen, $posEnd - $taglen);

                    $repl = ( ! empty($source)) ? $html[0] . $source . $html[1] : strtr($bbt[0] . $source . $bbt[1],
                                                                                        $par);
                    $source = $bbt[0] . $source . $bbt[1];

                } else
                {
                    $source = substr($str, $posStart, 10);
                    $repl = strtr($source, $par);
                }

            } else
            {
                $source = $bbt[0];
                $repl = $html[0];
            }

            $str = str_replace($source, $repl, $str);
            unset($source, $repl);

        }

        unset($bbt, $html);
    }

    unset($bbtag);

    return $str;
}


/**    Funzione per includere immagini da link.
 *    Può essere richiamata anche separatamente, ma prima bisogna filtrare la stringa con htmlentities().
 *
 *    Features:
 *    - Le immagini vengono ridimensionate alle dimensioni specificate ed in proporzione se le superano;
 *    - Le immagini possono essere caricate da tutti i domini o solo imageshack;
 *    - Su Altervista il controllo di ridimensione non funziona, pertanto in questo caso l'immagine non subisce ridimensionamenti;
 *    - Possibilità di specificare nuove dimensioni differenti da quelle di default col singolo richiamo della funzione.
 *
 *    TagList:
 *    - "[img]~[/img]"    Inclusione di immagini da link.
 *
 *
 * @param string $str
 * @param integer $maxWidth
 * @param integer $maxHeight
 * @param boolean $imageshack
 *
 * @return string
 */
function img($str)
{
    global $PARAMETERS;

    $maxWidth = $PARAMETERS['settings']['bbd']['image_max_width'];
    $maxHeight = $PARAMETERS['settings']['bbd']['image_max_height'];


    if ($PARAMETERS['settings']['bbd']['imageshack'] == 'OFF')
    {
        $regexpUrl = "https?://([-\w\.]+)+(:\d+)?(/([-\w/_\.]*(\?\S+)?)?\.(" . implode('|',
                                                                                       $PARAMETERS['settings']['bbd']['images_ext']) . "))?";
    } else
    {
        $regexpUrl = "https?://img[0-9]{2,3}\.imageshack\.us/img[0-9]{2,3}/[0-9]{3,4}/[-\w_\.]+\.(" . implode('|',
                                                                                                              $PARAMETERS['settings']['bbd']['images_ext']) . ")";
    }


    $sobstitution = array();
    preg_match_all("#\[img\]($regexpUrl)+\[/img\]#is", $str, $img);

    foreach ($img[0] as $key => $source)
    {
        list($width, $height) = @getimagesize($img[1][$key]);

        if ($width && $height)
        {
            if ($width >= $maxWidth)
            {
                $height = ($maxWidth * $height) / $width;
                $width = $maxWidth;

            } elseif ($height >= $maxHeight)
            {
                $width = ($maxHeight * $width) / $height;
                $height = $maxHeight;
            }

            $sobstitution[$source] = "<img src=\"" . $img[1][$key] . "\" style=\"width:" . $width . "px; height:" . $height . "px;\" alt=\"\" />";

        } else
        {
            $sobstitution[$source] = "<img src=\"" . $img[1][$key] . "\" alt=\"\" />";
        }

    }

    return strtr($str, $sobstitution);
}

?>