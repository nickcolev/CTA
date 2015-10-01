<?php
/*
*	Biorhythm by Till Gerken
*	http://www.zend.com/zend/tut/dynamic.php
*	Edited and optimized by Nick Kolev nickcolev@gmail.com on 04-Jul-12 from lampp demo
*/

	include $_SERVER[DOCUMENT_ROOT].'/lib.php';

function form () {

	htmlHeader ('Biorhythm');
	echo <<< HTML
<center>
<span class="ttl">Bio-rhytm</span>
<form name="data">
 <input type="hidden" name="size"/>
 <p>DOB: <input format="date" name="dob" placeholder="YYYY-MM-DD" onfocus="select()"/>
 <input type="submit" value=" OK "/></p>
</form>
</center>

<script>
document.data.size.value = document.body.clientWidth + "x" + document.body.clientHeight;
</script>

HTML;
	stdTail ();
	exit;
}

function mExit ($msg) {

	htmlHeader ('Biorhythm');
	print ($msg);
	stdTail ();
	exit;
}

function drawRhythm ($daysAlive, $period, $color) {

    global $daysToShow, $image, $diagramWidth, $diagramHeight;

	    // get day on which to center
	    $centerDay = $daysAlive - ($daysToShow / 2);

	    // calculate diagram parameters
	    $plotScale = ($diagramHeight - 25) / 2;
	    $plotCenter = ($diagramHeight - 25) / 2;

	    // draw the curve
	    for($x = 0; $x <= $daysToShow; $x++) {
		// calculate phase of curve at this day, then Y value
		// within diagram
		$phase = (($centerDay + $x) % $period) / $period * 2 * pi();
		$y = 1 - sin($phase) * (float)$plotScale + (float)$plotCenter;

		// draw line from last point to current point
		if ($x > 0)
		    imageLine($image, $oldX, $oldY,
			      $x * $diagramWidth / $daysToShow, $y, $color);

		// save current X/Y coordinates as start point for next line
		$oldX = $x * $diagramWidth / $daysToShow;
		$oldY = $y;
	    }
}


	// ---- MAIN PROGRAM START ----

	// check if we already have a date to work with,
	// if not display a form for the user to enter one
	$dob = trim ($_GET['dob']);
	if (!$dob) form ();

	if(!preg_match ('/^\d{4}\-\d{2}\-\d{2}$/', $dob))
		mExit ("<p>The date '$dob' is invalid.</p>");

    // Client screen size
    $aSize = explode ('x', $_GET[size]);
	// get different parts of the date
	$a = explode ('-', $dob);
	$birthDay = $a[2];
	$birthMonth = $a[1];
	$birthYear = $a[0];
	// specify diagram parameters (these are global)
	$diagramWidth = $width = $aSize[0] - 1;
	$diagramHeight = $height = $aSize[1] - 1;
	$daysToShow = 30;

	// calculate the number of days this person is alive
	// this works because Julian dates specify an absolute number
	// of days -> the difference between Julian birthday and
	// "Julian today" gives the number of days alive
	$daysGone = abs (gregorianToJD ($birthMonth, $birthDay, $birthYear)
			- gregorianToJD (date('m'), date ('d'), date ('Y')));

	// create image
	$image = imageCreate ($diagramWidth, $diagramHeight);

	// allocate all required colors
	$colorBackgr       = imageColorAllocate ($image, 0, 22, 0);
	$colorForegr       = imageColorAllocate ($image, 0, 30, 0);
	$colorGrid         = imageColorAllocate ($image, 127, 127, 127);
	$colorCross        = imageColorAllocate ($image, 128, 148, 110);
	$colorPhysical     = imageColorAllocate ($image, 255, 0, 0);
	$colorEmotional    = imageColorAllocate ($image, 0, 255, 0);
	$colorIntellectual = imageColorAllocate ($image, 64, 64, 255);

	// clear the image with the background color
	imageFilledRectangle ($image, 0, 0, $width - 1, $height - 1, $colorForegr);

	// calculate start date for diagram and start drawing
	$nrSecondsPerDay = 60 * 60 * 24;
	$diagramDate = time() - ($daysToShow / 2 * $nrSecondsPerDay)
		       + $nrSecondsPerDay;

	for ($i = 1; $i < $daysToShow; $i++)
	{
	    $thisDate = getDate($diagramDate);
	    $xCoord = ($diagramWidth / $daysToShow) * $i;

	    // draw day mark and day number
	    imageLine($image, $xCoord, $diagramHeight - 25, $xCoord,
		      $diagramHeight - 20, $colorGrid);
	    imageString($image, 3, $xCoord - 5, $diagramHeight - 16,
			$thisDate[ "mday"], $colorGrid);

	    $diagramDate += $nrSecondsPerDay;
	}

	// draw rectangle around diagram (marks its boundaries)
	imageRectangle ($image, 0, 0, $diagramWidth - 1, $diagramHeight - 20, $colorGrid);

	// draw middle cross
	imageLine ($image, 0, ($diagramHeight - 20) / 2, $diagramWidth,
		  ($diagramHeight - 20) / 2, $colorCross);
	imageLine ($image, $diagramWidth / 2, 0, $diagramWidth / 2, $diagramHeight - 20,
		  $colorCross);

	// Legend
	imageString ($image, 3, 10, 10,  "DOB:   $birthYear-$birthMonth-$birthDay", $colorCross);
	imageString ($image, 3, 10, 26,  'Today: '.date ('Y-m-d'),  $colorCross);
	imageString ($image, 3, 10, $diagramHeight - 42,  'Physical', $colorPhysical);
	imageString ($image, 3, 10, $diagramHeight - 58,  'Emotional', $colorEmotional);
	imageString ($image, 3, 10, $diagramHeight - 74,  'Intellectual', $colorIntellectual);

	// now draw each curve with its appropriate parameters
	drawRhythm ($daysGone, 23, $colorPhysical);
	drawRhythm ($daysGone, 28, $colorEmotional);
	drawRhythm ($daysGone, 33, $colorIntellectual);

	// set the content type
	header ('Content-type: image/png');

	// create an interlaced image for better loading in the browser
	imageInterlace ($image, 1);

	// mark background color as being transparent
	imageColorTransparent ($image, $colorBackgr);

	// now send the picture to the client (this outputs all image data directly)
	imagePNG ($image);
?>
