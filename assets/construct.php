<?php
include 'assets/helpers.php';
include 'assets/credentials.php';
include 'assets/title.php';
include 'assets/timeline.php';
include 'assets/measure.php';
include 'assets/list.php';
include 'assets/longgraph.php';

//logic and pageflow for log layout
function loadlog() {
	global $l;
	$hours = number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 1);

	switch ($l) {
		case 'home':
			home($hours);
			break;

		case 'divisions':
			$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
			measures($query, $hours, 'division');
			break;

		case 'tasks':
			$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
			measures($query, $hours, 'task');
			break;

		case 'projects':
			$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
			measures($query, $hours, 'project');
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
					echo '<span class="title">No such page exists in the Log.</span>';
					break;
			}
	}
}

//creates homepage
function home($h) {
	//title
	title('select sum(log.time) as hours, count(*) as logs from log;');

	//timeline
	timeline('select log.date, sum(log.time) as hours from log group by date order by log.id asc;');

	//120 day graph
	$days = 120;

	$now = new DateTime();
	$now = $now->format('Y-m-d');

	$old = new DateTime($now);
	$old = $old->sub(new DateInterval('P'.($days - 1).'D'));
	$old = $old->format('Y-m-d');

	$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$old."'".' and '."'".$now."'".'  order by log.id asc;';

	longgraph($query, $hours, $days, 0);

	//14 day graph
	$days = 14;

	$old = new DateTime($now);
	$old = $old->sub(new DateInterval('P'.($days - 1).'D'));
	$old = $old->format('Y-m-d');

	$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$old."'".' and '."'".$now."'".' order by log.id asc;';
	longgraph($query, $hours, $days, 0);

	echo '<div class="divider"></div>';

	//division measures
	echo'<a href="divisions" class="title">Divisions</a>';
	
	echo'<div class="spacer"></div>';

	$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
	measures($query, $h, 'division');
	echo '<div class="divider"></div>';

	//tasks measures
	echo'<a href="tasks" class="title">Tasks</a>';
	
	echo'<div class="spacer"></div>';

	$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
	measures($query, $h, 'task');
	echo '<div class="divider"></div>';

	//projects measures
	echo'<a href="projects" class="title">Projects</a>';
	
	echo'<div class="spacer"></div>';

	$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
	measures($query, $h, 'project');
}

//creates detailed page for project/task/division ($type) of given location ($l), using total hours ($h)
function spec($l, $type) {
	//get type of page
	if ($type == 'task') $typeOpp = 'project';
	else if ($type == 'project') $typeOpp = 'task';
	else if ($type == 'division') $typeOpp = 'project';

	//get full number of hours for percentage calculations
	$h = number_format(getnum('select sum(log.time) as hours from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';', 'hours'), 1);

	//title
	$query = 'select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
	title($query);

	//timeline
	$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';
	timeline($query);

	//graph
	//find extremity days for graph
	$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';

	$conn = connect();
	$result = $conn->query($query);

	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['hours']]);
		}

		//get timespan
		$first = new DateTime($rows[sizeof($rows)-1][0]);
		$last = new DateTime($rows[0][0]);
		$difference = $last->diff($first)->format("%a");

		//limit to 120
		if ($difference > 120) {
				$first = new DateTime($rows[0][0]);
				$first = $first->sub(new DateInterval('P'.(120).'D'));
				$difference = $last->diff($first)->format("%a");
		}

		$days = $difference;

		if ($days <= 0) $days = 1;

		$now = $last;
		$now = $now->format('Y-m-d');

		$old = new DateTime($now);
		$old = $old->sub(new DateInterval('P'.($days - 1).'D'));
		$old = $old->format('Y-m-d');

		$hours = getNum('select sum(time) as num_hours, '.$type.'.name as type from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where date between '."'".$old."'".' and '."'".$now."'".' and '.$type.'.name = '."'".$l."'".';', 'num_hours');

		if ($type = 'division') {
			 $query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$old."'".' and '."'".$now."'".' order by log.id asc;';
			longgraph($query, $hours, $days, 2);
		}
		else {
			$query = 'select division.name as title, log.date, log.time as hours, '.$type.'.name as type from log left join division on division.id = log.division_id join '.$type.' on '.$type.'.id = log.'.$type.'_id where date between '."'".$old."'".' and '."'".$now."'".' order by log.id asc;';
			longgraph($query, $hours, $days, 1);
		}
	}

	echo '<div class="divider"></div>';

	//if not division, make division measures
	if ($type != 'division') {
		echo'<a href="divisions" class="title">Divisions</a>';
		echo'<div class="spacer"></div>';
		$query = 'select '.$type.'.name as main, division.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
		measures($query, $h, 'division');
	} else {
		echo'<a href="projects" class="title">Projects</a>';
		echo'<div class="spacer"></div>';
	}
	
	//measure title
	if ($type == 'project') {
		echo '<div class="divider"></div>';
		echo'<a href="tasks" class="title">Tasks</a>';
		echo'<div class="spacer"></div>';
	} else if ($type == 'task') {
		echo '<div class="divider"></div>';
		echo'<a href="projects" class="title">Projects</a>';
		echo'<div class="spacer"></div>';
	}

	//measures
	$query = 'select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
	measures($query, $h, $typeOpp);

	echo '<div class="divider" style="margin-bottom: 75px;"></div>';

	//loglist
	$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';
	loglist($query);
}
?>