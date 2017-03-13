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
		while ($row =  $result->fetch_assoc()) {
			if ($row["hours"] > $size) $size = $row["hours"];
			echo
			'
			<div class="measure-container">
				<svg class="measure-circle">
					<circle cx="75" cy="75" r="' . round(($row["hours"]/$size * 60) + 2) . '" stroke="#fff" stroke-width="' . round(($row["hours"]/$size * 6) + 2) . '" fill="none"/>
				</svg>
				<div class="measure-info">
					<a href="#" class="measure-title">' . $row["title"] . '</a>
					<p class="measure-text">' . number_format($row["hours"], 0) . ' hours</p>
					<p class="measure-text">' . $row["logs"] . ' logs</p>
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
	$query = "select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;";
	measures($query);
}
?>