<?php
function connect() {
	global $servername;
	global $username;
	global $password;
	global $database;
	$conn = new mysqli($servername, $username, $password, $database);
	$conn->set_charset("utf8");
	return $conn;
}

//returns single number through request query ($q = query, $e = select result)
function getNum($q, $e) {
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

function checkType($l) {
	$conn = connect();

	$result = $conn->query('select * from task where name = '."'".$l."'".';');
	if ($result->num_rows > 0) {
		$conn->close();
		return 'task';
	}

	$result = $conn->query('select * from project where name = '."'".$l."'".';');
	if ($result->num_rows > 0) {
		$conn->close();
		return 'project';
	}

	$result = $conn->query('select * from division where name = '."'".$l."'".';');
	if ($result->num_rows > 0) {
		$conn->close();
		return 'division';
	}

	$conn->close();
	return null;
}

function getOppositeType($type) {
	if ($type == 'task') return 'project';
	else if ($type == 'project') return 'task';
	else if ($type == 'division') return 'project';
}

function createSvgArc($x, $y, $r, $startAngle, $endAngle) {
	if($startAngle > $endAngle){
		$s = $startAngle;
		$startAngle = $endAngle;
		$endAngle = $s;
	}

	if ($endAngle - $startAngle > M_PI*2) $endAngle = M_PI*1.99999;

	if ($endAngle - $startAngle <= M_PI) $largeArc = 0;
	else $largeArc = 1;

	 $arc = [
		'M', $x, $y,
		'L', $x + (cos($startAngle) * $r), $y - (sin($startAngle) * $r), 
		'A', $r, $r, 0, $largeArc, 0, $x + (cos($endAngle) * $r), $y - (sin($endAngle) * $r),
		'L', $x, $y
	];

	return join(' ', $arc);
}

function getDivisionRatio($contextPage, $contextType, $topic, $topicType) {
	if (!$contextPage) {
		$q = 'select '.$topicType.'.name as title, division.name as division, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$topicType.'.name = '."'".$topic."'".' group by division order by hours desc;';
	} else {
		$q = 'select '.$topicType.'.name as title, division.name as division, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$topicType.'.name = '."'".$topic."'".' and '.$contextType.'.name = '."'".$contextPage."'".' group by division order by hours desc;';
	}

	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['division'], $row['hours']]);
		}
		return $rows;
	}
	$conn->close();
}

function remap($x, $in_min, $in_max, $out_min, $out_max) {
	$result = ($x - $in_min) * ($out_max - $out_min) / ($in_max - $in_min) + $out_min;

	if ($result > $out_max) return $out_max;
	if ($result < $out_min) return $out_min;
	return $result;
}

function pluralize($s, $n) {
	if(fmod($n, 1) !== 0.00){
		return $s.'s';
	} else {
		if ($n == 1) return $s;
		else return $s.'s';
	}
}

function getAllHours($l, $type) {
	if (!$l) {
		$q = 'select sum(time) as num_hours from log;';
		return number_format(getNum($q, 'num_hours'), 0, '.', '');
	} else {
		$q = 'select sum(log.time) as num_hours from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
		return number_format(getnum($q, 'num_hours'), 1);
	}
}

function getAllLogs($l, $type) {
	if (!$l) {
		$q = 'select count(*) as num_logs from log;';
		return getNum($q, 'num_logs');
	} else {
		$q = 'select count(*) as num_logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
		return getnum($q, 'num_logs');
	}
}

function getAllDays($l, $type) {
	if (!$l) {
		$q = 'select count(distinct(date)) as num_days from log;';
		return getNum($q, 'num_days');
	} else {
		$q = 'select count(distinct(date)) as num_days from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
		return getnum($q, 'num_days');
	}
}

function getLastUpdate() {
	$now = new DateTime();
	$now = $now->sub(new DateInterval('P1D'));
	$q = 'select max(date) as num_date from log;';
	$recent = new DateTime(getnum($q, "num_date"));

	$r;

	$difference = $now->diff($recent)->format("%a");
	if ($difference <= 0) $r = "today";
	else if ($difference == 1) $r = $difference." day ago";
	else $r = $difference." days ago";

	return $r;
}

function getExtremeDate($l, $type, $end) {
	if (!$l) $q = 'select log.date from log';
	else $q = 'select log.date from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
		
	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, $row['date']);
		}

		if ($end == 0) $date = new DateTime($rows[sizeof($rows)-1]);
		else $date = $lastDate = new DateTime($rows[0]);
		$date = $date->format('Y.m.d');
	}

	$conn->close();

	if ($date == null) return null;
	else return $date;
}
?>