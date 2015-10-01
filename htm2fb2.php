<?php
/*
*	Title:	HTML to FB2 converter
*	Created:	2012-03-21
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	License:	AGPL
*/

	include 'epub.php';
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';
	include 'zip.php';


function addTag ($s) {

	preg_match ('/<(p|h\d)>/i', $s, $a);
	return ($a[1] ? $s.'</'.$a[1].'>' : $s);
}

function aRow ($s) {

	$a = explode ("\n", preg_replace ('/<(h\d|p|empty-line)/i', "\n<$1", $s));
	$r = Array ();
	foreach ($a as $s) {
		$s = trim ($s);
		if (!$s) continue;
		if (!preg_match ('/<(h\d|p)>(.*)<\/\\1>/i', $s)) $s = addTag ($s);
		$r[] = $s;
	}
	return $r;
}

function form () {

	echo htmlHeader ('HTM2FB2');
	echo <<< HTML
<center>
<form enctype="multipart/form-data" method="post">
 <table>
  <tr><td><b>HTML:</b></td><td><input type="file" name="htm"/></td><td><input type="submit" value=" OK "/></td></tr>
  <tr><td>Cover:</td><td><input type="file" name="jpg"/></td><td><input type="checkbox" name="zip" title="zip"/></td></tr>
 </table>
</form>
</center>

</body></html>
HTML;
	exit;
}

function preprocess ($html) {

	// Normalize (simplify)
	$s = strip_tags ($html, '<p><h1><h2><h3><h4><h5><h6><b><i><cite><code><table><tr><td><th><p/>');
	$s = preg_replace ('/(\r\n|\n)/', ' ', $s);
	$s = preg_replace ('|<table[^>]+>|i', '<table>', $s);
	$p = Array ('/<(\/?)b>/i', '/<(\/?)i>/i');
	$r = Array ('<$1strong>', '<$1emphasis>');
	$s = preg_replace ($p, $r, $s);
	$s = preg_replace ('| </|', '</', $s);
	$s = preg_replace ('| \.\.\.|', '...', $s);
	$s = preg_replace ('/<(p|h\d|table|td)[^>]+>/i', '<\\1>', $s);
	$s = preg_replace ('|<table>|i', '<p><table>', $s);
	$s = preg_replace ('/(<p\/>|<p>&nbsp;<\/p>|<p><\/p>)/i', '<empty-line/>', $s);
	return aRow ($s);
}

function getBody ($s) {

	// Parse
	$a = preprocess ($s);
	$l = 0;
	foreach ($a as $s) {
		if (preg_match ('/^<h/', $s)) {
			$n = (int) substr ($s, 2, 1);
			$s = preg_replace ('/<h\d>(.*)<\/h\d>/', '<title><p>\\1</p></title>', $s);
			if ($n > $l) {
				$r[] = spc ($l).'<section>'.$s;
				$l = $n;
			} else if ($n == $l) {
				$d = substr ($b, 0, 2*($l-1));
				$r[] = spc ($l - 1).'</section>'."\n".spc ($l - 1).'<section>'.$s;
			} else {
				$r[] = spc ($l - 1).'</section>'."\n".spc ($l - 2).'</section>'."\n".spc ($l - 2).'<section>'.$s;
				$l = $n;
			}
		} else
			$r[] = spc ($l).$s;
	}
	for (; $l>0; $l--) $r[] = spc ($l - 1).'</section>';
	return join ("\n", $r);
}

function getHeader ($s) {

	$a[encoding] = qCharset ($s);
	$a[author] = aAuthor (qMeta ($s, 'author'));
	$a[date] = qMeta ($s, 'date');
	$a[genre] = qMeta ($s, 'genre');
	$a[lang] = qMeta ($s, 'lang');
	$a[annotation] = qMeta ($s, 'description');
	$a[title] = qTitle ($s);
	// more here...
	// defaults
	if (!$a[lang]) $a[lang] = $a[encoding] == 'windows-1251' ? 'bg' : 'en';
	if (!$a[genre]) $a[genre] = 'Fiction';
	return $a;
}

function getFirstValue ($a) {

	for ($i=0; $i<count ($a); $i++)
		if ($a[$i]) return $a[$i];
}

function process ($aHtml, $aCover, $zip) {

	$f = preg_replace ('/\.html?$/i', '', $aHtml[name]).'.fb2';
	$s = join ('', file ($aHtml[tmp_name]));
	$a = preg_split ('/<body/msi', $s);
	$h = getHeader ($a[0]);
	$b = getBody (substr ($a[1], 1));
	// Generate
	$today = date ('Y-m-d');
	if ($aCover[name]) {
		$cp = "\n      <coverpage>\n        <image xlink:href=\"#$aCover[name]\"/>\n      </coverpage>";
		$bin = "\n  <binary id=\"$aCover[name]\" content-type=\"$aCover[type]\">\n".
			chunk_split (base64_encode (join ('', file ($aCover[tmp_name])))).
			"  </binary>";
	}
	if ($h[annotation])
		$annotation = "\n      <annotation>\n$h[annotation]\n      </annotation>";
	$s = <<< HTML
<?xml version="1.0" encoding="{$h[encoding]}"?>
 <FictionBook xmlns:xlink="http://www.w3.org/1999/xlink"
  xmlns="http://www.gribuser.ru/xml/fictionbook/2.0">
  <description>
    <title-info>
      <genre match="100">fiction</genre>
      <author>
        <first-name>{$h[author][first]}</first-name>
        <middle-name>{$h[author][mid]}</middle-name>
        <last-name>{$h[author][last]}</last-name>
      </author>
      <book-title>{$h[title]}</book-title>
      <date>{$h[date]}</date>
      <lang>{$h[lang]}</lang>{$cp}{$annotation}
    </title-info>
    <document-info>
      <author>
        <first-name/>
        <last-name/>
        <nickname>vera5.com</nickname>
      </author>
      <program-used>PHP</program-used>
      <date value="{$today}">{$today}</date>
      <version>1.0</version>
    </document-info>
  </description>
  <body>
{$b}
  </body>{$bin}
 </FictionBook>
HTML;
	$s = postprocess ($s);
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

function qCharset ($s) {

	$r = 'iso-8859-1';
	if (!preg_match ('/<meta.*http-equiv=(.*)>/i', $s, $a)) return $r;
	preg_match ('/charset=(.*)"/i', $a[0], $a);
	if (!$a[1]) return $r;
	$a = preg_split ('/\s/', $a[1]);
	return preg_replace ('/["\']/', '', $a[0]);
}

function qMeta ($s, $name) {

	if (!preg_match ("/<meta (.*)name=\"$name\"(.*)>/i", $s, $a)) return; //exit ("err: can't figure $name <small>(tag &lt;meta name=&quot;$name&quot;... not found)</small>");
	$s = getFirstValue ($a);
	preg_match ('/content="(.*)"/i', $s, $a);
	if ($a[1]) return $a[1];
	exit ("err: can't find $name value");
}

function qTitle ($s) {

	if (!preg_match ('|<title>(.*)</title>|i', $s, $a)) exit ("err: tag &lt;title&gt; not found");
	return $a[1];
}

function spc ($n) { return substr ('              ', 0, 4 + (2*$n)); }


	if ($_FILES[htm]) process ($_FILES[htm], $_FILES[jpg], $_POST[zip]);
	else form ();
?>
