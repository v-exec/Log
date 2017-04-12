<?php
include 'assets/credentials.php';

//creates connection to database
function connect() {
	global $servername;
	global $username;
	global $password;
	global $database;
	$conn = new mysqli($servername, $username, $password, $database);
	return $conn;
}

//returns single number through request query ($q = query, $e = select result)
function getnum($q, $e) {
	$conn = connect();
	$r = "";
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$r = $row[$e];
	}
	$conn->close();
	return $r;
}

//creates the measures for tasks/projects of given query ($q)
function measures($q) {
	$conn = connect();
	$size = 1;
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['title'], $row['hours'], $row['logs']]);
		}

		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $size) $size = $rows[$i][1];
		}

		for ($i = 0; $i < sizeof($rows); $i++) {
			if (number_format($rows[$i][1], 0) == 1) $hourphrase = 'hour';
			else $hourphrase = 'hours';
			if ($rows[$i][2] == 1) $logphrase = 'log';
			else $logphrase = 'logs';

			echo
			'
			<div class="measure-container">
				<svg class="measure-circle">
					<circle cx="75" cy="75" r="'.round(($rows[$i][1]/$size * 60) + 2).'" stroke="#fff" stroke-width="'.round(($rows[$i][1]/$size * 6) + 2).'" fill="none"/>
				</svg>
				<div class="measure-info">
					<form id="'.$rows[$i][0].'" action="log" method="get"><input type="hidden" name="location" value="'.$rows[$i][0].'"></form>
					<a href="javascript:void(0);" class="measure-title" onclick="document.getElementById('."'".$rows[$i][0]."'".').submit();">'.$rows[$i][0].'</a>
					<p class="measure-text">'.number_format($rows[$i][1], 0).' '.$hourphrase.'</p>
					<p class="measure-text">'.$rows[$i][2].' '.$logphrase.'</p>
				</div>
			</div>
			';
		}
	}
	$conn->close();
}

//creates loglist of given query ($q)
function loglist($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		echo '<div class="spacer"></div>';
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['time'], $row['project'], $row['task'], $row['details']]);
		}

		for ($i = 0; $i < sizeof($rows); $i++) {
			echo
			'
			<div class="loglist-container">
				<div class="loglist-date">
					<span class="loglist-text">'.$rows[$i][0].'</span>
				</div>
				<div class="loglist-time">
					<span class="loglist-text">'.number_format($rows[$i][1], 1).'</span>
				</div>
				<div class="loglist-info">
					<form id="'.$rows[$i][2].'" action="log" method="get"><input type="hidden" name="location" value="'.$rows[$i][2].'"></form>
					<a href="javascript:void(0);" class="loglist-text" onclick="document.getElementById('."'".$rows[$i][2]."'".').submit();">'.$rows[$i][2].'</a>
				</div>
				<div class="loglist-info">
					<form id="'.$rows[$i][3].'" action="log" method="get"><input type="hidden" name="location" value="'.$rows[$i][3].'"></form>
					<a href="javascript:void(0);" class="loglist-text" onclick="document.getElementById('."'".$rows[$i][3]."'".').submit();">'.$rows[$i][3].'</a>
				</div>
				<div class="loglist-details">
					<span class="loglist-text">'.$rows[$i][4].'</span>
				</div>
			</div>
			';
		}
	}
	$conn->close();
}

//creates timeline of given project/task through query ($q)
function timeline($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, $row['date']);
		}

		echo
		'
		<div class="spacer" style="height: 15px;"></div>
		<span class="timeline-date-begin">'.$rows[sizeof($rows)-1].'</span>
		<span class="timeline-date-end">'.$rows[0].'</span>
		<div class="timeline-container">
			<div class="timeline"></div>
			<div class="timeline-marker-begin"></div>
			<div class="timeline-circle-container">
		';

		$first = new DateTime($rows[sizeof($rows)-1]);
		$last = new DateTime($rows[0]);
		$difference = $last->diff($first)->format("%a");

		for ($i = 0; $i < sizeof($rows); $i++) {
			$now = new DateTime($rows[$i]);
			$position = ($now->diff($first)->format("%a")) / $difference;

			if ($now != new DateTime($rows[$i - 1])) {
				echo
				'
				<svg class="timeline-circle" style="left: '. $position * 100 .'%;">
					<circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2.7" fill="#070707"/>
				</svg>
				';
			}
		}
		echo
		'
		</div>
		<svg class="timeline-marker-end">
			<live x1="0" y1="0" x2="0" y2="10" stroke-width="4"/>
		</svg>
		</div>
		';
	}
	$conn->close();
}

//creates detailed page for project/task ($type) of given location ($l)
function spec($l, $type) {
	if ($type == 'task') $typeOpp = 'project';
	else $typeOpp = 'task';

	$conn = connect();
	$result = $conn->query('select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';');

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$data = [$row['hours'], $row['logs']];
	}

	if (number_format($data[0], 0) == 1) $hourphrase = 'hour';
	else $hourphrase = 'hours';
	if ($data[1] == 1) $logphrase = 'log';
	else $logphrase = 'logs';
	
	echo
	'
	<div class="spacer"></div>
	<form id="'.$l.'" action="log" method="get"><input type="hidden" name="location" value="'.$l.'"></form>
	<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'".$l."'".').submit();">'.$l.'</a>
	<div class="spec-stats">
		<span class="spec-text">'.number_format($data[0], 0).' '.$hourphrase.'</span>
		<span class="spec-text">'.$data[1].' '.$logphrase.'</span>
	</div>
	';

	timeline('select log.date from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' order by date desc;');

	measures('select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;');

	echo '<div class="divider"></div>';

	loglist('select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' order by date desc;');

	$conn->close();
}

//checks if given location ($l) is project or task
function checkType($l) {
	$conn = connect();
	//true = task / false = project
	$type = null;
	$result = $conn->query('select * from task where name = '."'".$l."'".';');

	if ($result->num_rows > 0) {
		$type = true;
	} else $type = false;

	$conn->close();
	return $type;
}

//logic and pageflow for log layout
function loadlog() {
	global $location;
	if ($location == 'tasks') {
		$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by log.id;';
		measures($query);

	} else if ($location == 'projects') {
		$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by log.id;';
		measures($query);

	} else if ($location == 'logs') {
		$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id left join task on task.id = log.task_id order by date desc;';
		loglist($query);

	} else {
		if (checkType($location)) spec($location, 'task');
		else spec($location, 'project');
	}
}
?>