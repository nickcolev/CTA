<?php
/*
*	Title:	Simple cloud text editor
*	Author:	Nick Kolev (nickcolev@gmail.com)
*	Created: 2012-11-15 09:09
*
*/
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function open () {

	htmlHeader ('Edit');
	echo <<< HTML
<form enctype="multipart/form-data" method="post" name="data">
 <table align="center">
  <tr><td><input type="file" name="file"/> <input type="submit" value="Open"/></td></tr>
 </table>
</form>

HTML;
	stdTail ();
	exit;
}

function edit ($aFile) {

	if (!preg_match ('|^text/|', $aFile[type])) exit ("<script>\nalert ('Please select a text file');\nhistory.go(-1);\n</script>");
	htmlHeader ('Edit '.$aFile[name]);
	$s = join ('', file ($aFile[tmp_name]));
	echo <<< HTML
<script type="text/javascript">
var fChng = false;
function chng () {
  if (fChng) return;
  var o = document.getElementById ("name");
  o.style.color = "#FF0000";
  fChng = true;
}
function get () {
  document.location = "{$_SERVER[PHP_SELF]}";
}
function put () {
  if (!fChng) return false;
  var o = document.getElementById ("name");
  o.style.color = '';
  fChng = false;
  return true;
}
</script>

<center>
<form enctype="multipart/form-data" method="post" name="data" onsubmit="return put()">
 <input type="hidden" name="file" value="{$aFile[name]}"/>
 <table border=0 cellpadding=2 cellspacing=0 style="height:98%;width:100%;">
  <tr><td><input type="button" value="Open" onclick="get()"/> <input type="submit" value="Save"/> <span id="name">{$aFile[name]}</span></td></tr>
  <tr height="98%"><td><textarea name="data" style="height:100%;width:100%;font-family:monospace;font-size:9pt;color:#FFFFFF;background-color:transparent;" onkeyup="chng()">{$s}</textarea></td></tr>
 </table>
</form>
</center>

HTML;
	stdTail ();
}

function save ($file, $data) {

	header ('Content-Type: text/plain; charset=windows-1251');
	header ('Content-Length: '.strlen ($data));
	header ('Content-Disposition: attachment; filename='.$file);
	exit ($data);
}

	if ($_POST[data]) save ($_POST[file], $_POST[data]);
	if (!$_FILES[file]) open ();
	edit ($_FILES[file]);
?>
