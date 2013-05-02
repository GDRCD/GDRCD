/** * Script per tooltip
	* @author Blancks
*/

/** * Gestore del box di descrizione
*/
function show_desc(e, txt)
{
	curX = (document.all)? event.clientX + document.body.scrollLeft : e.pageX;
	curY = (document.all)? event.clientY + document.body.scrollTop : e.pageY;
	
	set_fade('descriptionLoc', 0);
	$('descriptionLoc').style.display='block';
	$('descriptionLoc').style.left=(parseInt(curX)-parseInt($('maincontent').offsetLeft)+tooltip_offsetX)+'px';
	$('descriptionLoc').style.top=(parseInt(curY)-parseInt($('maincontent').offsetTop)+tooltip_offsetY)+'px';
	$('descriptionLoc').innerHTML=txt;
	
	start_fade('descriptionLoc', '+');
}


function hide_desc()
{
	$('descriptionLoc').style.display='none';
}
