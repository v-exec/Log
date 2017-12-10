<?php
include 'assets/helpers.php';
include 'assets/credentials.php';
include 'assets/title.php';
include 'assets/timeline.php';
include 'assets/measure.php';
include 'assets/list.php';
include 'assets/graph.php';

$type = checkType($l);

function loadlog() {
	global $l;
	global $type;

	$hours = getAllHours(null, null);

	switch ($l) {
		case 'home':
			home($hours);
			break;

		case 'divisions':
			measures($l, 'division', null, $hours);
			break;

		case 'tasks':
			measures($l, 'task', null, $hours);
			break;

		case 'projects':
			measures($l, 'project', null, $hours);
			break;

		default:
			if ($type != null) spec();
			else echo '<span style="display: block; border-bottom: solid 1px #ccc; width: 100%; padding: 40px; font-size: 24px;">No such page exists in the Log.</span>';
			break;
	}
}

function home($h) {
	global $l;
	global $type;

	title($l, $type);
	timeline($l, $type);

	graph($l, 120, $type, $h);
	graph($l, 14, $type, $h);

	measures($l, 'division', null, $h);
	measures($l, 'task', null, $h);
	measures($l, 'project', null, $h);
}

function spec() {
	global $l;
	global $type;

	$h = getAllHours($l, $type);

	title($l, $type);
	timeline($l, $type);
	graph($l, 0, $type, $h);

	if ($type != 'division') {
		measures($l, 'division', $type, $h);
		measures($l, getOppositeType($type), $type, $h);
	} else {
		measures($l, 'task', $type, $h);
		measures($l, 'project', $type, $h);
	}

	loglist($l, $type, 10);
}
?>