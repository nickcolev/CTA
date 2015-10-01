<?php
/*
*	Title:	eTXT to HTM converter
*	Created:	2012-09-15
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	echo hdr ('eTXT2HTM');
	echo <<< HTML
<center>
<h3>eTXT to HTM</h3>
<form enctype="multipart/form-data" method="post">
 <table>
  <tr><td>Title:</td><td><input name="title"/></td></tr>
  <tr><td>Author:</td><td><input name="author"/></td></tr>
  <tr><td>eText:</td><td><input type="file" name="txt"/></td></tr>
  <tr><td colspan=2><hr/></td></tr>
  <tr><td>&nbsp;</td><td align="right"><input type="submit" value=" OK "/></td></tr>
 </table>
</form>
</center>

</body></html>
HTML;
	exit;
}

function hdr ($title, $author=NULL) {

	if (!$title) $title = 'eTXT2HTM';
	return <<< HTML
<html><head><title>{$title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
<meta name="author" content="{$author}"/>
<style type="text/css">
p.txt	{ text-align: justify; text-indent: 3em; }
</style>
</head>
<body>


HTML;
}

function frontpg ($title, $author) {

	return <<< HTML
<center>
<h1>{$title}</h1>
<p>{$author}</p>
</center>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
HTML;
}

function normQt ($s) { return preg_replace ('/"/', '&quot;', $s); }


	if (!$_FILES[txt][name]) form ();

	// Verify input
	if ($_FILES[txt][type] != 'text/plain') eAbort ('Expected text file, but <tt>'.$_FILES[txt][type].'</tt> passed.');

	$a = file ($_FILES[txt][tmp_name]);
	for ($i=0; $i<count ($a); $i++) {
		$s = trim ($a[$i]);
		if (strlen ($s) == 0) continue;
		if ($s == $_POST[title]) { $title = $s; continue; }
		if ($s == $_POST[author]) { $author = $s; continue; }
		if (preg_match ("/^>\t/", $s)) {
			$r .= '<h2>'.normQt (substr ($s, 2)).'</h2>'.CR;
		} else
			$r .= '<p class="txt">'.normQt ($s).'</p>'.CR;
//if ($i>300) break;
	}

	$r = preg_replace ("|</h2>\n<h2>|", ': ', $r);
	$r = preg_replace ('|<h2>|', '<p>&nbsp;</p>'.CR.'<h2>', $r);

	$h = hdr ($title, $author).frontpg ($title, $author);
	echo <<< HTML
{$h}

{$r}

</body></html>
HTML;
?>
