/** * La funzione permette di aprire la land in popup direttamente dal login
	* @author Blancks
*/
function check_login()
{
	if (!$('allow_popup').checked)
	{
		$('popup').value = '0';
		$('do_login').target = '_top';
		$('do_login').submit();
	
	}else
	{
		window.open('', 'MYGDR', 'toolbar=0,menubar=0,directories=0,location=0,scrollbars=yes,status=0,resizable=1');
		
		$('popup').value = '1';
		$('do_login').target = 'MYGDR';
		$('do_login').submit();
	}
}