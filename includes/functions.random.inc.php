<?php
/**
 * Questo modulo fornisce una serie di funzioni per ottenere valori randomici
 */

/**
 * Genera una password casuale crittograficamente sicura.
 *
 * Utilizza il set di caratteri definito da `GDRCD_RANDOM_PASSWORD`
 * (speciali, numerici, lettere maiuscole e minuscole),
 * che corrisponde a un alfabeto di 85 simboli.
 *
 * Una password con meno di 8 caratteri è considerata insicura
 *
 * @param int $length Lunghezza della password da generare. Default: valore di GDRCD_RANDOM_PASSWORD_LENGTH
 * @return string Password casuale generata.
 */
function gdrcd_random_password(int $length = GDRCD_RANDOM_PASSWORD_LENGTH): string
{
    return gdrcd_random_string($length, GDRCD_RANDOM_PASSWORD);
}

/**
 * Genera una stringa casuale della lunghezza specificata.
 *
 * Il set di caratteri utilizzato è determinato da una combinazione di flag bitmask.
 * I flag disponibili sono le costanti `GDRCD_RANDOMSTRING_*`.
 *
 * La funzione è crittograficamente sicura e può essere utilizzata per generare password o tokens.
 *
 * @param int $length Lunghezza della stringa da generare.
 * @param int $flags Bitmask dei flag che definiscono i caratteri consentiti. Default: tutti i caratteri.
 * @return string Stringa casuale generata.
 */
function gdrcd_random_string(int $length, int $flags = GDRCD_RANDOMSTRING_ALL): string
{
    $allowed_characters_map = [];

    if ($flags & GDRCD_RANDOMSTRING_SPECIALS) {
        $allowed_characters_map = [
            ...$allowed_characters_map,
            33,
            ...range(35,38),
            ...range(40,43),
            45, 47,
            ...range(60,64),
            ...range(91,95),
            123, 125
        ];
    }

    if ($flags & GDRCD_RANDOMSTRING_NUMBERS) {
        $allowed_characters_map = [...$allowed_characters_map, ...range(48,57)];
    }

    if ($flags & GDRCD_RANDOMSTRING_LOWERCASE_LETTERS) {
        $allowed_characters_map = [...$allowed_characters_map, ...range(97,122)];
    }

    if ($flags & GDRCD_RANDOMSTRING_UPPERCASE_LETTERS) {
        $allowed_characters_map = [...$allowed_characters_map, ...range(65,90)];
    }

    if ($flags & GDRCD_RANDOMSTRING_QUOTES) {
        $allowed_characters_map = [...$allowed_characters_map, 34, 39];
    }

    $allowed_characters_map = array_values(array_unique($allowed_characters_map));
    $allowed_characters_max_index = count($allowed_characters_map) - 1;

    $buffer = '';

    for ($i = 0; $i < $length; ++$i) {
        $buffer .= chr($allowed_characters_map[random_int(0,$allowed_characters_max_index)]);
    }

    return $buffer;
}

/**
 * Genera una stringa casuale composta solo da lettere.
 *
 * @param int $length Lunghezza della stringa da generare.
 * @param bool $case_insensitive Se `true`, include sia lettere maiuscole che minuscole. Default: `false` (solo maiuscole).
 * @return string Stringa casuale composta da lettere.
 */
function gdrcd_random_string_letters(int $length, bool $case_insensitive = false): string
{
    $flags = $case_insensitive
        ? GDRCD_RANDOMSTRING_LETTERS_CI
        : GDRCD_RANDOMSTRING_LETTERS;

    return gdrcd_random_string($length, $flags);
}

/**
 * Genera una stringa casuale alfanumerica (lettere e numeri).
 *
 * @param int $length Lunghezza della stringa da generare.
 * @param bool $case_insensitive Se `true`, include sia lettere maiuscole che minuscole. Default: `false` (solo maiuscole).
 * @return string Stringa casuale alfanumerica.
 */
function gdrcd_random_string_alpha(int $length, bool $case_insensitive = false): string
{
    $flags = $case_insensitive
        ? GDRCD_RANDOMSTRING_ALPHA_CI
        : GDRCD_RANDOMSTRING_ALPHA;

    return gdrcd_random_string($length, $flags);
}

/**
 * Genera una stringa casuale composta solo da cifre numeriche (0-9).
 *
 * @param int $length Lunghezza della stringa da generare.
 * @return string Stringa casuale numerica.
 */
function gdrcd_random_string_numbers(int $length): string
{
    return gdrcd_random_string($length, GDRCD_RANDOMSTRING_NUMBERS);
}
