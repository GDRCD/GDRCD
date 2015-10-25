/** * La funzione permette di aprire la land in popup direttamente dal login
 * @author Blancks
 */
function check_login() {
    if (!gdrcd_selector('allow_popup').checked) {
        gdrcd_selector('popup').value = '0';
        gdrcd_selector('do_login').target = '_top';
        gdrcd_selector('do_login').submit();

    } else {
        window.open('', 'MYGDR', 'toolbar=0,menubar=0,directories=0,location=0,scrollbars=yes,status=0,resizable=1');

        gdrcd_selector('popup').value = '1';
        gdrcd_selector('do_login').target = 'MYGDR';
        gdrcd_selector('do_login').submit();
    }
}
