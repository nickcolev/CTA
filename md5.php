<?php
/*
*	Title:	md5 encoder
*	Created:	2012-11-11
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo <<< HTML
<center>
<form>
 <input type="text" name="s" title="token"/>
 <input type="submit" value=" OK "/>
</form>
</center>
HTML;
}


	htmlHeader ('MD5');
	if ($_GET[s])
		echo '<pre><span title="token">'.$_GET[s].'</span> =&gt; <span title="MD5">'.md5 ($_GET[s]).'</span></pre>';
	else
		form ();
?>

</body></html>
