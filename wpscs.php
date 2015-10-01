<HTML><HEAD><TITLE>WPS CheckSum</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<LINK href="screen.css" rel="stylesheet" type="text/css" />
<SCRIPT type="text/javascript">
function calccs() {
	var o = document.getElementById('num');
	if (o.value) {
		var n = normalizeInput();
		if (n != null)
			document.getElementById("result").innerText = "PIN: "+(o.value.substr(0,1)=='0'?'0':'')+n+chksum(n);
	} else {
		alert("Please enter a value");
	}
}
function chksum(pin) {
	var acu = 0;
	while (pin > 0) {
		acu += 3 * (pin % 10);
		pin = Math.floor(pin/10);
		acu += pin % 10;
		pin = Math.floor(pin/10);
	}
	return (10 - acu % 10) % 10;
}
function hex2int(s) {
	s = s.replace(/:/g, '');	// remove potential ':'
	if (s.length < 6) {
		alert("Bad input hex value length");
		return null;
	}
	s = s.substr(s.length - 6, 6);
	return parseInt(s,16);
}
function normalizeInput() {
	var s = document.getElementById('num').value.toUpperCase();
	if (isNaN(s)) {
		// HEX?
		if (s.match(/[0-9]|[A-F]/))		// hex?
			return hex2int(s);
		else {
			alert("Bad input hex value");
			return null;
		}
	} else {
		if (s.length < 7) {
			alert("Bad input value length");
			return null;
		}
		s = s.substr(0,7);
	}
	return parseInt(s,10);
}
function setInput() {
	var e = window.event;
	document.getElementById('num').value = e.target.innerText;
}
</SCRIPT>
</HEAD>
<BODY>

<!--
<p>Test:
<a href="#" onclick="setInput()">500d55</a>,
<a href="#" onclick="setInput()">50:0d:55</a>,
<a href="#" onclick="setInput()">82:39:4D:50:0d:55</a>,
<a href="#" onclick="setInput()">9352723</a>,
<a href="#" onclick="setInput()">93527231</a>,
<a href="#" onclick="setInput()">9352</a>,
<a href="#" onclick="setInput()">04840954</a>
</p>
-->

<p align="center">WPS PIN:
<input id="num" value="9352723" title="The first 7-digits of the WPS PIN"/>
<button onclick="calccs()">Calc</button></p>

<pre id="result"></pre>

</BODY></HTML>
