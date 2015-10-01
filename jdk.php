<?php
	include $_SERVER[DOCUMENT_ROOT].'/lib.inc';
	define ("JDK", 'C:/Progra~1/Java/jdk1.7.0_04/bin');
	define ("LIB", 'C:/Progra~1/Java/jdk1.7.0_04');

function form () {

	stdHeader (basename (PHPself ()));
	echo <<< HTML
<center>
<form enctype="multipart/form-data" method="post">
 <input type="file" name="s"/>
 <input type="submit" value="Compile"/>
</form>
</center>

HTML;
	stdTail ();
	exit;
}

function cc ($a) {

	// Get source code
	$src = join ('', file ($a[tmp_name]));
	// Figure class name
	preg_match ('/public class (.*) ?{/', $src, $a);
	if (!$a[1]) exit ('err: can\t figure class name');
	$class = trim ($a[1]);
	// Set tmp disk file for compilation
	$fname = $class.'.java';
	$fp = fopen ($fname, 'w');
	fwrite ($fp, $src, strlen ($src));
	fclose ($fp);
	// Set compiler cmd
	$cmd = JDK.'/javac.exe -classpath '.LIB.' '.$fname." 2>&1";
//exit ($cmd);
	$s = system ($cmd, $e);
	// Check exit code and either display error or return the result
	if ($e) {
		header ('Content-Type: text/plain');
		$fp = fopen ('php://stderr', 'r') or die ('can\'t get stderr');
		while (!feof ($fp)) echo fgets ($fp, 4096);
		fclose ($fp);
		exit;
	}
	stdHeader ('JDK');
	$u = PHPself ();
	echo '<p>'.$fname.' compiled. <a href="'.$u.'?c='.$fname.'">Run</a>? <a href="'.$u.'?d='.$fname.'" title="class">Download</a>?</p>';
//echo "cmd=$cmd<br/>e=$e<pre>".$src.'</pre>';
}

function dnload ($fname) {

	$class = preg_replace ('/\.java$/', '.class', $fname);
	if (!file_exists ($class)) exit ('can\t find class '.$class);
	download ($class);
}

function jrun ($fname) {

	$class = preg_replace ('/\.java$/', '', $fname);
	if (!file_exists ($class.'.class')) exit ('can\t find class '.$class);
	$cmd = JDK.'/java.exe '.$class;
//exit ($cmd);
	$s = exec ($cmd, $a, $e);
	if ($e) echo 'err '.$e;
	echo '<pre>';
	foreach ($a as $s) echo $s."\n";
	echo '</pre>';
}

	// Go to OS tmp folder
	chdir (dirname (tempnam ('/tmp', 'jc')));
	// Dispatch
	if ($_GET[c]) jrun ($_GET[c]);
	else if ($_GET[d]) dnload ($_GET[d]);
	else if ($_FILES[s]) cc ($_FILES[s]);
	else form ();

?>