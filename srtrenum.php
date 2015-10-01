<?php
/*
*	Renumber a SRT file
*/
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo <<< HTML
<form name="data" method="post" enctype="multipart/form-data">
 <p align="center">
  <input type="text" name="dx" size=3 title="Number dx" style="text-align:right;"/>
  <input type="file" name="srt"/>
  <input type="submit" value=" OK ">
 </p>
</form>

</body></html>
HTML;
	exit;
}

function process ($s, $dx) {

	return (preg_match ('/^\d+$/', $s) ? $s + $dx : $s).CRLF;
}


	htmlHeader ('SRT Renumber');
	if (!$_FILES['srt']['name']) form ();
	if (!preg_match ('/\.srt/i', basename ($_FILES['srt']['name']))) eAbort ('Only SRT files are supported');
	if (!preg_match ('/\-?\d{1,}/', $_POST['dx'])) eAbort ('Invalid renumber shift value (must be integer)');
	$a = file ($_FILES['srt']['tmp_name']);
	echo '<pre>';
	foreach ($a as $s)
		echo process (trim ($s), $_POST['dx']);
	echo '</pre>';
?>


</body></html>
