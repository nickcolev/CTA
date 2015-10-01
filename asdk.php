<?php
/*
*	Android SDK interface
*
*	Flow
*	1. Set target
*	2. Create project
*	3. Make (ant)
*	4. Debug/Run
*/
	include $_SERVER[DOCUMENT_ROOT].'/lib.php';


function getTarget () {

	htmlHeader('ASDK');
	$cmd = escapeshellcmd('/cgi-bin/footer');
	echo shell_exec($cmd);
	echo "e=$e";
/*
	$s = exec('/home/nick/asdk/tools/android list targets', $a, $e);
echo "s=$s, e=$e"; adump($a);
*/
	echo <<< HTML

<center>
<form enctype="multipart/form-data" method="post">
 <input type="file" name="f"/>
 <input type="submit" value=" OK "/>
</form>
</center>
HTML;
}

	if (!$_GET[target]) getTarget ();
?>
