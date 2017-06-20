<?php
include 'assets/logcredentials.php';
include 'assets/loginterfaces.php';

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
	$type = null;

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

	return null;
}

//logic and pageflow for log layout
function loadlog() {
	global $l;

	switch ($l) {
		case 'home':
			home();
			break;

		case 'division':
			$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
			measures($query, number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 0));
			break;

		case 'tasks':
			$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
			measures($query, number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 0));
			break;

		case 'projects':
			$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
			measures($query, number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 0));
			break;

		case 'logs':
			$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id left join task on task.id = log.task_id order by log.id asc;';
			loglist($query, false);
			break;

		default:
			switch (checkType($l)) {
				case 'division':
					spec($l, 'division');
					break;

				case 'project':
					spec($l, 'project');
					break;

				case 'task':
					spec($l, 'task');
					break;

				default:
					echo '<span class="spec-title">No such page exists in the Log.</span>';
					break;
			}
	}
}
?>