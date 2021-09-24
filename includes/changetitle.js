/**
 * Funziona di modifica ciclica del titolo per la segnalazione dei nuovi pm
 * @author Blancks
 * @author Breaker
 */
var titleInterval = null;
var titleOriginal = null;

function blink_title(text, blink) {
    if (titleOriginal == null)
        titleOriginal = document.title;

    if (blink)
        document.title = text;
    else
        document.title = titleOriginal;


    titleInterval = setTimeout("blink_title('" + text + "', " + (blink ? "false" : "true") + ")", 999);
}

function flashTitle(pageTitle, newMessageTitle) {
    if (document.title == pageTitle) {
        document.title = newMessageTitle;
    }
    else {
        document.title = pageTitle;
    }
}

//setInterval("flashTitle('Titolo 1', 'Nuovo Titolo')", 800);

function stop_blinking_title() {
    if (titleInterval != null) {
        clearTimeout(titleInterval);
        document.title = titleOriginal;
        titleInterval = null;
        titleOriginal = null;
    }
}
