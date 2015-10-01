<?php
/*
*	Title:	Text ebook to FB2 converter
*	Created:	2012-11-21
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include 'epub.php';
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';
	include 'zip.php';


function form () {

	htmlHeader ('etxt2fb2');
	echo <<< HTML
<center>
<form enctype="multipart/form-data" method="post">
 <table>
  <tr><td><b>eText:</b></td><td><input type="file" name="file" title="expecting SFB format"/></td><td><input type="submit" value=" OK "/></td></tr>
  <tr><td>Cover:</td><td><input type="file" name="jpg"/></td><td><input type="checkbox" name="zip" title="zip"/></td></tr>
 </table>
</form>
</center>

</body></html>
HTML;
	exit;
}

function process ($aFile, $zip) {

	$a = etxt2a ($aFile[file]);
	$cover = cover ($aFile[jpg]);
	$s = a2fb2 ($a, $cover);
	$s = postprocess ($s);
	$f = fb2ext ($aFile[file][name]);
	if ($zip) {
		$oZip = new zipfile ();
		$oZip->add_file ($s, basename ($f));
		$s = $oZip->file ();
		$f .= '.zip';
	}
	// Result to the client (might be zipped)
	header ('Content-Type: application/octet-stream');
	header ('Content-Length: '.strlen ($s));
	header ('Content-Disposition: attachment; filename="'.$f.'"');
	echo $s;
}

function postprocess ($s) {

	// Special HTML chars to XML chars
		$a = array (
			'&quot;' => '&#34;',
			'&copy;' => '&#169;',
			'&mdash;' => '&#8212;',
			'&eacute;' => '&#233;'
		);
		foreach ($a as $k => $v) $s = preg_replace ("/$k/", $v, $s);
	return $s;
}


	if ($_FILES[file]) process ($_FILES, $_POST[zip]);
	else form ();
?>
