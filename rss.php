<?php
/*
*	Title:	RSS reader
*	Created:	2012-08-23
*	Author:	Nick Kolev nickcolev@gmail.com
*	License:	AGPL
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';
	define ("DEFAULT_URL", 'http://feeds.bbci.co.uk/news/rss.xml');
	define ("DEFAULT_TTL", 'BBC');

function aNews ($s) {

	$r = Array ();
	preg_match_all ('!<item>(.*)</item>!msUi', $s, $a);
	foreach ($a[1] as $s) {
		$t = item (trim ($s));
		if (!$t[title]) continue;
		$k = strtotime ($t[pubDate]);
		$r[$k] = $t;
	}
	krsort ($r);
	reset ($r);
	return $r;
}

function aRss () {

	$e = 'http://www.economist.com/rss';
	$n = 'http://rss.nytimes.com/services/xml/rss/nyt';
	return Array (
		DEFAULT_TTL => DEFAULT_URL,
		'Google News' => 'http://news.google.com/news?ned=us&topic=h&output=rss',
		'Economist World' => $e.'/the_world_this_week_rss.xml',
		'Economist Briefing' => $e.'/briefings_rss.xml',
		'Economist SciTech' => $e.'/science_and_technology_rss.xml',
		'NYT US' => $n.'/HomePage.xml',
		'NYT Global' => $n.'/GlobalHome.xml',
		'NYT World' => $n.'/World.xml',
		'NYT Tech' => $n.'/Technology.xml'
	);
}

function form ($r) {

	$self = basename (PHPself ());
	$rss = $_COOKIE[rss] ? $_COOKIE[rss] : $r[BBC];
	foreach ($r as $k => $v)
		$s .= ($s ? ', ' : '').'<a class="click" onclick="rss(this)" title="'.$v.'">'.$k.'</a>';
	echo <<< HTML
<hr size=1/>

<script type="text/javascript">
function rss (o) {
  document.data.rss.value = o.title;
  document.location = "{$self}?rss=" + escape (o.title);
}
function vfy () {
  return (document.data.rss.value ? true : false);
}
</script>

<form name="data" onsubmit="vfy()">
 <p>RSS:
 <input name="rss" onfocus="select()" value="{$rss}"/>
 <input type="submit" value=" OK "/>
 </p>
</form>

<p class="tiny">Suggestions:<br/>
{$s}</p>

HTML;
}

function get ($u) {

	$fp = fopen ($u, 'r') or die ('Can\'t open '.$u.' (try <a href="'.PHPself ().'?rss='.DEFAULT_URL.'">'.DEFAULT_TTL.'</a>)');
	while (!feof ($fp)) $s .= fread ($fp, 4096);
	fclose ($fp);
	// Check if it is valid RSS
	if (!preg_match ('/<rss /msU', $s)) eAbort ('not RSS resource '.$u);
	return utfEncode ($s, 'w');
}

function isGood ($t) {

	$s = $t[title].' '.$t[description];
	return (preg_match ('/(asap|codeign|drupal|flash|iphone|joomla|magento|prestashop|PSD|Smarty|Test proj|urgent|wordpress)/i', $s) ? false : true);
}

function item ($s) {

	$s = norm ($s);
	$t = Array ('category', 'description', 'link', 'pubDate', 'title');
	foreach ($t as $k)
		if (preg_match ("!<$k>(.*)</$k>!msU", $s, $a)) $r[$k] = strip_tags (trim (preg_replace ('/\]\]>/', '', $a[1])), '<br>');
	// Remove leading <br> in the description (if any)
	$r[description] = preg_replace ('|^<br ?/?>|', '', $r[description]);
	return $r;
}

function norm ($s) {

	$p = Array ('/&lt;/', '/&gt;/', '/&apos;/', '/&#39;/', '/&nbsp;/', '/&amp;/');
	$r = Array ('<', '>', '\'', '\'', ' ', '&');
	return preg_replace ($p, $r, $s);
}

function process () {

	$r = aRss ();
	$u = $_COOKIE[rss] ? $_COOKIE[rss] : $r[BBC];
	$s = get ($u);
	// RSS title
	echo ttl ($s);
	// RSS data
	$a = aNews ($s);
	echo '<dl class="rss">'.CR;
	foreach ($a as $t) {
		if ($t[link]) $t[title] = '<a href="'.$t[link].'">'.$t[title].'</a>';
		if ($u == 'http://www.freelancer.com/rss/notify_nickkolev.xml')
			if (!isGood ($t)) continue;
		echo '<dt class="rss" title="'.$t[pubDate].'">'.$t[title].'</dt><dd class="rss">'.$t[description].'</dd>'.CR;
		++$cnt;
	}
	echo '</dl>'.CR;
	echo '<p class="tiny">'.$cnt.' cases</p>'.CR.CR;
	form ($r);
}

function ttl ($s) {

	$a = explode ('<item>', $s);
	$s = trim ($a[0]);
	$a = item ($s);
	$t = $a[title];
	if ($a[link]) $t = '<a href="'.$a[link].'">'.$t.'</a>';
	return	'<h3><small>'.$a[pubDate].'</small><br/>'.$t.'</h3>'.CR.CR;
}


	if ($_GET[rss]) {
		$u = $_GET[rss];
		if (!preg_match ('|^http://|i', $u)) $u = 'http://'.$u;
		setCookie ('rss', $u, time () + (3600 * 24 * 7));
		header ('Location: '.PHPself ());
		exit;
	}

	htmlHeader ('RSS');
	process ();
	stdTail ();
?>
