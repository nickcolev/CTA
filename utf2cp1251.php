<?php
/*
*	Title:	utf-8 to windows-1251 converter
*	Created:	2012-12-24
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo htmlHeader ('utf2cp1251');
	echo <<< HTML
<center>
<h3>UTF-8 to CP1251 convertor</h3>
<form enctype="multipart/form-data" method="post">
 <p><input type="file" name="txt" title="Select input file"/> <input type="submit" value=" OK "/></p>
</form>
</center>

</body></html>
HTML;
	exit;
}



	if (!$_FILES[txt][name]) form ();

	$s = join ('', file ($_FILES[txt][tmp_name]));
	$s = utfEncode ($s, 'w');
	$s = preg_replace ('/^\xEF\xBB\xBF/', '', $s);
	$s = preg_replace ("/\r\n/", "\n", $s);
	header ('Content-Type: application/octet-stream');
	header ('Content-Length: '.strlen ($s));
	header ('Content-Disposition: attachment; filename='.basename ($_FILES[txt][name]));
	echo $s;
?>
