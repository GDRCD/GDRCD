<?php

/**
 * Stampa un messaggio d'errore
 *
 * @param string $message Il messaggio da stampare, viene filtrato con gdrcd_filter_out
 * @param bool $filter_out default true. Se false disattiva il filtraggio interno con gdrcd_filter_out\
 * @param bool $return default false. Se true ritorna l'output formattato invece di stamparlo direttamente
 * @return mixed
 */
function gdrcd_error(string $message, bool $filter_out = true, bool $return = false): mixed
{
    if ($filter_out) {
        $message = gdrcd_filter_out($message);
    }

    $output = <<<HTML
        <div class="error">{$message}</div>
    HTML;

    if ($return) {
        return $output;
    }

    echo $output;
    return null;
}

/**
 * Stampa un messaggio d'errore e termina lo script
 *
 * @param string $message Il messaggio da stampare, viene filtrato con gdrcd_filter_out
 * @param bool $filter_out default true. Se false disattiva il filtraggio interno con gdrcd_filter_out
 * @return void
 */
function gdrcd_error_exit(string $message, bool $filter_out = true): void
{
    gdrcd_error($message, $filter_out);
    die();
}

/**
 * Stampa un messaggio d'errore con header e footer e termina lo script
 *
 * @param string $message Il messaggio da stampare, viene filtrato con gdrcd_filter_out
 * @param bool $filter_out default true. Se false disattiva il filtraggio interno con gdrcd_filter_out
 * @return void
 */
function gdrcd_error_fatal(string $message, bool $filter_out = true): void
{
    gdrcd_basic_page(gdrcd_error($message, $filter_out, true));
}
