<!doctype html>
<html><head><title>MD5</title>
</head>
<body>

<?php
function form () {

	echo <<< HTML
<center>
<form>
 <input type="text" name="s" title="token"/>
 <input type="submit" value=" OK "/>
</form>
</center>
HTML;
}


	if ($_GET[s])
		echo '<pre><span title="token">'.$_GET[s].'</span> =&gt; <span title="MD5">'.md5 ($_GET[s]).'</span></pre>';
	else
		form ();
?>

</body></html>
