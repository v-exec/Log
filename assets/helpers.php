<?php
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

//checks if given l ($l) is project, task, or division
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

//creates svg arc
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

//gets percentages of each divison within given query ($q)
function getDivisionRatio($q) {
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

//simple range mapping function
function remap($x, $in_min, $in_max, $out_min, $out_max)
{
	$result = ($x - $in_min) * ($out_max - $out_min) / ($in_max - $in_min) + $out_min;

	if ($result > $out_max) return $out_max;
	if ($result < $out_min) return $out_min;
	return $result;
}

//returns string ($s) of appropriate pluralization in relation to number $(n)
function pluralize($s, $n) {
	if ($n == 1) return $s;
	else return $s.'s';
}
?>