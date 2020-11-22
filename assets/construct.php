<?php
include 'assets/helpers.php';
include 'assets/credentials.php';
include 'assets/title.php';
include 'assets/timeline.php';
include 'assets/measure.php';
include 'assets/list.php';
include 'assets/graph.php';

$type = checkType($clean);

function loadlog() {
	global $type;
	global $clean;

	$hours = getAllHours(null, null);

	if ($clean == 'home') home($hours);
	else if ($type != null) spec();
	else echo '<div class="divider"></div><span style="display: block; border-bottom: solid 1px #ccc; width: 100%; padding: 40px; font-size: 24px;">No such page exists in the Log.</span>';
}

function home($h) {
	global $type;
	global $clean;

	title($clean, $type);
	timeline($clean, $type);

	graph($clean, 0, 90, $type, $h);
	graph($clean, 0, 14, $type, $h);

	measures($clean, 'division', null, $h);
	measures($clean, 'task', null, $h);
	measures($clean, 'project', null, $h);
}

function spec() {
	global $type;
	global $clean;

	$h = getAllHours($clean, $type);

	title($clean, $type);
	timeline($clean, $type);
	graph($clean, 0, 90, $type, $h);

	if ($type != 'division') {
		measures($clean, 'division', $type, $h);
		measures($clean, getOppositeType($type), $type, $h);
	} else {
		measures($clean, 'task', $type, $h);
		measures($clean, 'project', $type, $h);
	}

	loglist($clean, $type, 10);
}
?>