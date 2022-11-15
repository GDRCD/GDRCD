<?php

class Filters extends BaseClass
{
    /**
     * @fn gdrcd_filter
     * @note Funzione per il filtraggio dei dati
     * @param string $type
     * @param mixed $val
     * @return mixed
     */
    public static function gdrcd_filter(string $type, mixed $val): mixed
    {

        switch ( strtolower($type) ) {
            case 'in':
            case 'get':
                $val = addslashes(str_replace('\\', '', $val));
                break;

            case 'num':
            case 'int':
                $val = (int)$val;
                break;

            case 'out':
                $val = html_entity_decode($val, ENT_HTML5, 'utf-8');
                break;

            case 'addslashes':
                $val = addslashes($val);
                break;

            case 'email':
                $val = (preg_match("#^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$#i", $val)) ? $val : false;
                break;

            case 'includes':
                $val = (preg_match("#[^:]#i")) ? htmlentities($val, ENT_QUOTES) : false;
                break;

            case 'url':
                $val = urlencode($val);
                break;

            case 'fullurl':
                $val = filter_var(str_replace(' ', '%20', $val), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
                break;

            case 'html':
                $notAllowed = [
                    "#(<script.*?>.*?(<\/script>)?)#is" => "Script non consentiti",
                    "#(<iframe.*?\/?>.*?(<\/iframe>)?)#is" => "Frame non consentiti",
                    "#(<object.*?>.*?(<\/object>)?)#is" => "Contenuti multimediali non consentiti",
                    "#(<embed.*?\/?>.*?(<\/embed>)?)#is" => "Contenuti multimediali non consentiti",
                    "#([o,O][N,n](.*?)=(.*?)\"?'?[^\s\"']+'?\"?)#is" => " ",
                    "#(javascript:[^\s\"']+)#is" => "",
                ];

                if ( $GLOBALS['PARAMETERS']['settings']['html'] == HTML_FILTER_HIGH ) {
                    $notAllowed = array_merge($notAllowed, [
                        "#(<img.*?\/?>)#is" => "Immagini non consentite",
                        "#(url\(.*?\))#is" => "none",
                    ]);
                }

                $val = preg_replace(array_keys($notAllowed), array_values($notAllowed), $val);
                break;

            case 'string':
                $val = htmlspecialchars($val);
                break;
        }

        return $val;
    }

    /**
     * @fn html
     * @note Filtro di tipo html
     * @param string|null $val
     * @return mixed
     */
    public static function html(?string $val): string
    {
        return self::gdrcd_filter('html', $val);
    }

    /**
     * @fn out
     * @note Filtro di tipo out
     * @param string|null $val
     * @return string
     */
    public static function out(?string $val): string
    {
        return self::gdrcd_filter('out', $val);
    }

    /**
     * @fn in
     * @note Filtro di tipo in
     * @param string|null $val
     * @return mixed
     */
    public static function in(string|null $val): string
    {
        return self::gdrcd_filter('in', $val);
    }

    /**
     * @fn int
     * @note Filtro di tipo int
     * @param string|null|int $val
     * @return int
     */
    public static function int(string|int|null $val): int
    {
        return self::gdrcd_filter('int', $val);
    }

    /**
     * @fn int
     * @note Filtro di tipo int
     * @param int|null|bool $val
     * @return bool
     */
    public static function bool(int|null|bool $val): bool
    {
        return (bool)self::gdrcd_filter('int', $val);
    }

    /**
     * @fn url
     * @note Filtro di tipo url
     * @param string|null $val
     * @return string
     */
    public static function url(?string $val): string
    {
        return self::gdrcd_filter('url', $val);
    }

    /**
     * @fn email
     * @note Filtro di tipo email
     * @param string|null $val
     * @return string
     */
    public static function email(?string $val): string
    {
        return self::gdrcd_filter('email', $val);
    }

    /**
     * @fn get
     * @note Filtro di tipo get
     * @param string|null $val
     * @return string
     */
    public static function get(?string $val): string
    {
        return self::gdrcd_filter('get', $val);
    }

    /**
     * @fn text
     * @note Filtro per testi
     * @param string|null $val
     * @return string
     */
    public static function text(?string $val): string
    {
        return nl2br(trim(self::gdrcd_filter('out', $val)));
    }

    /**
     * @fn date
     * @note Funzione di rendering delle date in base al formato
     * @param string|null $val
     * @param string|null $format
     * @return string
     */
    public static function date(?string $val, ?string $format): string
    {
        if ( !empty($val) ) {
            return date($format, strtotime($val));
        }

        return '';
    }

    /**
     * @fn checkbox
     * @note Filtro di tipo checkbox per inserimento in db
     * @param string|null $val
     * @return int
     */
    public static function checkbox(?string $val): int
    {
        return !empty($val) ? 1 : 0;
    }

    /**
     * @fn string
     * @note Filtro di tipo string per estrazione da db
     * @param string|null $val
     * @return string
     */
    public static function string(?string $val): string
    {
        return self::gdrcd_filter('string', $val);
    }
}