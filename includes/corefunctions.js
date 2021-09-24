/** * Libreria javascript di base
 * @author Blancks
 */

/** * Funzione selettore rapido per JS
 * @author Blancks
 */
function gdrcd_selector(id) {
    return document.getElementById(id);
}

/** * Gestore del fade in/out
 * @author Blancks
 */
function start_fade(id, op) {
    if (op == '+') {
        set_fade(id, 0);
        toggle_fade(id, 0, op);
    } else
        toggle_fade(id, 100, op);
}

function toggle_fade(id, opacity, op) {
    if (opacity <= 100 && opacity >= 0) {
        set_fade(id, opacity);
        opacity = (op == '+') ? opacity + 5 : opacity - 5;

        setTimeout("toggle_fade('" + id + "'," + opacity + ", '" + op + "')", 20);
    }
}

function set_fade(id, opacity) {
    var obj = gdrcd_selector(id);

    obj.style.filter = "alpha(opacity:" + opacity + ")";
    obj.style.KHTMLOpacity = opacity / 100;
    obj.style.MozOpacity = opacity / 100;
    obj.style.opacity = opacity / 100;

}
