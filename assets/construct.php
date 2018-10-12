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

	switch ($clean) {
		case 'home':
			home($hours);
			break;

		case 'divisions':
			measures($clean, 'division', null, $hours);
			break;

		case 'tasks':
			measures($clean, 'task', null, $hours);
			break;

		case 'projects':
			measures($clean, 'project', null, $hours);
			break;

		default:
			if ($type != null) spec();
			else echo '<span style="display: block; border-bottom: solid 1px #ccc; width: 100%; padding: 40px; font-size: 24px;">No such page exists in the Log.</span>';
			break;
	}
}

function home($h) {
	global $type;
	global $clean;

	title($clean, $type);
	timeline($clean, $type);

	graph($clean, 90, $type, $h);
	graph($clean, 14, $type, $h);

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
	graph($clean, 0, $type, $h);

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