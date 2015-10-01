<?php
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo <<< HTML
<form name="data" method="post" enctype="multipart/form-data">
 <p align="center">
  <input type="text" name="dt" size=3 title="Time shift in seconds" style="text-align:right;"/>
  <input type="file" name="srt" title="SRT file"/>
  <input type="submit" value=" OK ">
 </p>
</form>

</body></html>
HTML;
	exit;
}

function s2t ($s) {		// convert string hh:mm:ss.nnn to float mmm.nnn

	$a = explode (',', $s);
	$d = $a[1];
	$a = explode (':', $a[0]);
	return (3600 * $a[0] + 60 * $a[1] + (float) ($a[2].'.'.$d));
}

function process ($s, $dt) {

	if (!preg_match ("/\-\->/", $s)) return $s;
	$a  = explode ('-->', $s);
	$t1 = trim ($a[0]); $t2 = trim ($a[1]);
	return tshift ($t1, $dt).' --> '.tshift ($t2, $dt).CRLF;
}

function t2s ($f) {		// convert float time to string hh:mm:ss.nnn

	$h  = (int) floor ($f / 3600);
	$f -= $h * 3600;
	$m  = (int) floor ($f / 60);
	$f -= $m * 60;
	$s  = (int) $f;
	$f -= $s;
	$n  = (int) ($f * 1000);
	return str_pad ($h, 2, '0', STR_PAD_LEFT).':'.str_pad ($m, 2, '0', STR_PAD_LEFT).':'.str_pad ($s, 2, '0', STR_PAD_LEFT).','.str_pad ($n, 3, '0', STR_PAD_RIGHT);
}

function tshift ($s, $dt) { return t2s (s2t ($s) + $dt); }


	htmlHeader ('SRT shift');
	if (!$_FILES['srt']['name']) form ();
	if (!preg_match ('/\.srt/i', basename ($_FILES['srt']['name']))) eAbort ('Only SRT files are supported');
	if (!preg_match ('/\d+(\.\d+)?/', $_POST['dt'])) eAbort ('Invalid time shift value');
	$a = file ($_FILES['srt']['tmp_name']);
	echo '<pre>';
	foreach ($a as $s)
		echo process ($s, $_POST['dt']);
	echo '</pre>';
?>


</body></html>
