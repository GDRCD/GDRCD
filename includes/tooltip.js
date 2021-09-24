/** * Script per tooltip
 * @author Blancks
 */

/** * Gestore del box di descrizione
 */
function show_desc(e, txt) {
    curX = (document.all) ? event.clientX + document.body.scrollLeft : e.pageX;
    curY = (document.all) ? event.clientY + document.body.scrollTop : e.pageY;

    set_fade('descriptionLoc', 0);
    gdrcd_selector('descriptionLoc').style.display = 'block';
    gdrcd_selector('descriptionLoc').style.left = (parseInt(curX) - parseInt(gdrcd_selector('maincontent').offsetLeft) + tooltip_offsetX) + 'px';
    gdrcd_selector('descriptionLoc').style.top = (parseInt(curY) - parseInt(gdrcd_selector('maincontent').offsetTop) + tooltip_offsetY) + 'px';
    gdrcd_selector('descriptionLoc').innerHTML = txt;

    start_fade('descriptionLoc', '+');
}


function hide_desc() {
    gdrcd_selector('descriptionLoc').style.display = 'none';
}
