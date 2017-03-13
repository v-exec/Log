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

//creates the measures for all tasks/projects, or select measures acording to selection query ($q)
function measures($q) {
	$conn = connect();
	$size = 0;
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row["title"], $row["hours"], $row["logs"]]);
		}

		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $size) $size = $rows[$i][1];
		}

		for ($i = 0; $i < sizeof($rows); $i++) {
			echo
			'
			<div class="measure-container">
				<svg class="measure-circle">
					<circle cx="75" cy="75" r="' . round(($rows[$i][1]/$size * 60) + 2) . '" stroke="#fff" stroke-width="' . round(($rows[$i][1]/$size * 6) + 2) . '" fill="none"/>
				</svg>
				<div class="measure-info">
					<a href="#" class="measure-title">' . $rows[$i][0] . '</a>
					<p class="measure-text">' . number_format($rows[$i][1], 0) . ' hours</p>
					<p class="measure-text">' . $rows[$i][2] . ' logs</p>
				</div>
			</div>
			'
			;
		}
	}
	$conn->close();
}

//logic for log loading
function loadlog() {
	global $location;
	if ($location == "tasks") {
		$query = "select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;";
		measures($query);
	} else if ($location == "projects") {
		$query = "select project.name as title, sum(log.time) as hours, log.date as time, count(*) as logs from log left join project on project.id = log.project_id group by title order by date desc;";
		measures($query);
	} else if ($location == "logs") {
	} else {
	}
}
?>