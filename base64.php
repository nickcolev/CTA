<?php
/*
*	Title:	base64 encoder
*	Created:	2012-11-11
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo <<< HTML
<center>
<form enctype="multipart/form-data" method="post">
 <input type="file" name="f"/>
 <input type="submit" value=" OK "/>
</form>
</center>
HTML;
}


	htmlHeader ('base64');
	if ($_FILES[f])
		echo '<pre>'.chunk_split (base64_encode (join ('', file ($_FILES[f][tmp_name])))).'</pre>';
	else
		form ();
?>

</body></html>
