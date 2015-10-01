<?php
/*
*	Title:	epub lib
*	Created:	2012-10-29
*	Author:	Nick Kolev nickcolev@gmail.com
*	License:	AGPL
*
*	Functions '*2a' return the following array:
*	[author]
*	[title]
*	[encoding]
*	[text] which is array of sections
*/

function a2fb2 ($a, $cover64) {

	$auth = aAuthor ($a[author]);
	$today = date ('Y-m-d');
	foreach ($a[text] as $sect) $s .= fb2sect ($sect);
	$s = preg_replace ("/\r\n/", "\n", $s);
	if ($cover64) {
		$cover = CR.'      <coverpage><image xlink:href="#cover.jpg"/></coverpage>';
		$bin = CR.'  <binary id="cover.jpg" content-type="image/jpeg">'.CR.$cover64.'</binary>';
	}
	return <<< HTML
<?xml version="1.0" encoding="{$a[encoding]}"?>
 <FictionBook xmlns:xlink="http://www.w3.org/1999/xlink"
  xmlns="http://www.gribuser.ru/xml/fictionbook/2.0">
  <description>
    <title-info>
      <genre match="100">fiction</genre>
      <author>
        <first-name>{$auth[first]}</first-name>
        <middle-name>{$auth[mid]}</middle-name>
        <last-name>{$auth[last]}</last-name>
      </author>
      <book-title>{$a[title]}</book-title>
      <date>{$a[date]}</date>
      <lang>{$a[lang]}</lang>{$cover}{$cp}{$annotation}
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
{$s}
  </body>{$bin}
 </FictionBook>
HTML;
}

function aAuthor ($s) {

	$a = explode (' ', $s);
	$r = array ();
	$r[first] = $a[0];
	$r[last] = $a[2] ? $a[2] : $a[1];
	if (count ($a) > 2) $r[mid] = $a[1];
	return $r;
}

function etxt2a ($aFile) {

	if ($aFile[type] != 'text/plain') eAbort ($aFile[name].' is not a text file');
	$a = file ($aFile[tmp_name]);
	if (count ($a) < 3) eAbort ('unusual file passed');
	$k = 0;
	// Expecting: First line -- Author, Second -- Title
	for ($i=0; $i<count ($a); $i++) {
		if (preg_match ("/^(\r\n|\n)/", $a[$i])) continue;
		if (!$author) { $author = trim (normPara ($a[$i])); continue; }
		if (!$title) {
			$title = trim (normPara ($a[$i]));
			continue;
		} else if (preg_match ('/^\|/', $a[$i])) {
				$title .= ' '.trim (normPara ($a[$i]));
				continue;
		}
		$a[$i] = normPara ($a[$i]);
		// Chapter? (section)
		if (preg_match ('/^>/', $a[$i])) {
			$s = trim (substr ($a[$i], 1));
			++$i;
			while (preg_match ('/^>/', $a[$i]) && !preg_match ('/^>>/', $a[$i]) && $i < count ($a)) {
				$s .= ' '.trim (substr ($a[$i], 1));
				++$i;
			}
			$k++;
			$r[$k][title] = trim (preg_replace ('/^>/', '', $s));
			continue;
		}
		// Verse?
		if (preg_match ('/^[ACLPS]>/', $a[$i])) {
			$p = '/'.substr ($a[$i], 0, 1).'\$/';
			$verse = trim (substr ($a[$i], 2));
			++$i;
			while (!preg_match ($p, $a[$i]) && $i<count ($a)) {
				$verse .= trim (normPara ($a[$i]))."\n";
				++$i;
			}
			$r[$k][] = trim ($verse);
			continue;
		}
		$r[$k][] = trim (preg_replace ('/^@/', '', $a[$i]));
		//if ($i > 1000) break;	// Test
	}
	// Defaults
	if (!$encoding) {	// Try to figure out from a paragraph in the middle of the first section
		$a = $r[0] ? $r[0] : $r[1];
		$i = (int) (count ($a) / 2);
		$s = trim ($a[$i]);
		$encoding = preg_match ('/[Åå]/', $s) ? 'windows-1251' : 'utf-8';
	}
	if (!$title) {	// Try to figure it out from the file name
		$s = preg_replace ('/\.\w+$/', '', $aFile[name]);
		$a = preg_split ('/[\-]/', $s);
		if ($a[1]) { $author = trim ($a[0]); $title = trim ($a[1]); }
		else $title = trim ($a[0]);
echo 'Title:'; adump ($a);
	}
	return Array ('title' => $title, 'author' => $author, 'encoding' => $encoding, 'text' => $r);
}

function cover ($a) {

	if (!$a[name]) return;
	if (!preg_match ('!image/jpeg!i', $a[type])) eAbort ('The cover should be JPG/PNG');
	return chunk_split (base64_encode (join ('', file ($a[tmp_name]))));
}

function fb2ext ($s) {

	$i = strrpos ($s, '.');
	return substr ($s, 0, $i).'.fb2';
}

function fb2sect ($a) {

	for ($i=0; $i<count ($a); $i++) {
		if (!$a[$i]) continue;
		$s .= "\t\t".'<p>'.(preg_match ("/\n/", $a[$i]) ? fb2stanza ($a[$i]) : $a[$i]).'</p>'.CR;
	}
	return	"\t".'<section>'.CR.
		"\t\t".'<title><p>'.$a[title].'</p></title>'.CR.
		$s.
		"\t".'</section>'.CR;
}

function fb2stanza ($s) {

	$a = explode ("\n", $s);
	foreach ($a as $s) $r .= '<v>'.$s.'</v>';
	return '<poem><stanza>'.$r.'</stanza></poem>';
}

function htm2a ($aFile) {	// under development

	if ($aFile[type] != 'text/html') eAbort ($aFile[name].' is not a HTML file');
	$a = file ($aFile[tmp_name]);
	$k = 0;
//adump ($a);
	for ($i=0; $i<count ($a); $i++) {
		$a[$i] = trim ($a[$i]);
		if (preg_match ('|<title>(.*)</title>|i', $a[$i], $t))
			$title = $t[1];
		else if (preg_match ('|charset=(.*)|i', $a[$i], $t))
			$charset = preg_replace ('/[">]/', '', $t[1]);
		else if (preg_match ('|<h(\d)(.*)>(.*)</h\\1>|i', $a[$i], $t))
adump ($t);
	}
}

function normPara ($s) {	// Normalize Paragraph

	$p = Array (
		'/^@/', '/^\|/',	// e-text BOL
		'/  /', '/__/',		// doubled
		'/…/', '/ - /', '/^- /',	// weird
		'/\( /', '/ \)/', '/(\S)\(/', '/([,\.])(\w)/',	// punctuation
		'/"/', '/“/'	// quotes
	);
	$r = Array (
		'', '',
		' ', '_',
		'...', ' — ', '— ',
		'(', ')', '\\1 (', '\\1 \\2',
		'”', '”'
	);
	return preg_replace ($p, $r, $s);
}
?>